<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Helper sederhana untuk mencatat jejak aktivitas (audit trail)
 * yang dilakukan oleh Admin maupun Superadmin.
 */
class AuditLogger
{
    public static function log(
        string $action,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null,
    ): ?AuditLog {
        try {
            return AuditLog::create([
                'user_id'       => $userId ?? auth()->id(),
                'action'        => $action,
                'question_type' => $subject ? class_basename($subject) : null,
                'record_id'     => $subject?->getKey(),
                'old_values'    => $oldValues ?: null,
                'new_values'    => $newValues ?: null,
                'ip_address'    => request()->ip(),
                'user_agent'    => mb_substr((string) request()->userAgent(), 0, 255),
            ]);
        } catch (Throwable $e) {
            // Audit trail tidak boleh menggagalkan aksi utama.
            report($e);

            return null;
        }
    }
}
