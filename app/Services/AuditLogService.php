<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Record an audit log entry.
     */
    public static function log(string $action, ?string $description = null, ?int $userId = null, ?string $userName = null): AuditLog
    {
        $user = Auth::user();
        $finalUserId = $userId ?? $user?->id;
        $finalUserName = $userName ?? $user?->name ?? 'System';

        return AuditLog::create([
            'user_id' => $finalUserId,
            'user_name' => $finalUserName,
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
