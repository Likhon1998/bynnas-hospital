<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SystemAuditor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

class SystemBackupController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $driver = Config::get('database.default');
        $connection = Config::get('database.connections.'.$driver, []);

        return view('admin.system.backup', [
            'driver' => $driver,
            'database' => $connection['database'] ?? null,
        ]);
    }

    public function download(Request $request, SystemAuditor $auditor): BinaryFileResponse|Response
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $driver = Config::get('database.default');
        $connection = Config::get('database.connections.'.$driver, []);
        $stamp = now()->format('Ymd-His');

        if ($driver === 'sqlite') {
            $path = $connection['database'] ?? database_path('database.sqlite');

            abort_unless(is_string($path) && File::exists($path), 404, 'SQLite database file was not found.');

            $auditor->record('system.backup', 'Downloaded SQLite database backup.', null, [
                'driver' => 'sqlite',
                'path' => basename($path),
            ], $request->user());

            return response()->download($path, 'hospital-backup-'.$stamp.'.sqlite');
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $backupDir = storage_path('app/backups');
            File::ensureDirectoryExists($backupDir);
            $target = $backupDir.DIRECTORY_SEPARATOR.'hospital-backup-'.$stamp.'.sql';

            $process = new Process([
                'mysqldump',
                '--user='.($connection['username'] ?? ''),
                '--password='.($connection['password'] ?? ''),
                '--host='.($connection['host'] ?? '127.0.0.1'),
                '--port='.(string) ($connection['port'] ?? 3306),
                '--single-transaction',
                '--routines',
                '--triggers',
                (string) ($connection['database'] ?? ''),
            ]);
            $process->setTimeout(120);
            $process->run();

            abort_unless($process->isSuccessful(), 500, 'mysqldump failed: '.$process->getErrorOutput());

            File::put($target, $process->getOutput());

            $auditor->record('system.backup', 'Downloaded MySQL database backup.', null, [
                'driver' => $driver,
                'database' => $connection['database'] ?? null,
            ], $request->user());

            return response()->download($target, basename($target))->deleteFileAfterSend(true);
        }

        abort(422, 'Database backup is only available for SQLite and MySQL connections.');
    }
}
