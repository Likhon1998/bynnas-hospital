<?php

namespace App\Console\Commands;

use App\Support\WorkInbox;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('work:sync {--escalate : Escalate unacknowledged critical items}')]
#[Description('Refresh role work queues and optionally escalate stale critical alerts')]
class WorkInboxSyncCommand extends Command
{
    public function handle(WorkInbox $inbox): int
    {
        $inbox->syncAll();

        if ($this->option('escalate')) {
            $escalated = $inbox->escalateOverdue();
            $this->info('Work queues synced. Escalated '.$escalated.' critical items.');

            return self::SUCCESS;
        }

        $this->info('Work queues synced.');

        return self::SUCCESS;
    }
}
