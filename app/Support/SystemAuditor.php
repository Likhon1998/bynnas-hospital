<?php

namespace App\Support;

use App\Models\SystemAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class SystemAuditor
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, string $summary, ?Model $subject = null, array $context = [], ?User $actor = null): SystemAuditLog
    {
        $actor ??= Auth::user();

        return SystemAuditLog::query()->create([
            'user_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'summary' => $summary,
            'context' => $context === [] ? null : $context,
            'ip_address' => Request::ip(),
        ]);
    }
}
