<?php

namespace App\Observers;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat jejak aktivitas (audit trail) untuk setiap perubahan model
 * yang dilakukan dari panel Admin, panel Superadmin, maupun API.
 */
class AuditObserver
{
    protected const REDACTED = ['created_at', 'updated_at', 'remember_token', 'password'];

    public function created(Model $model): void
    {
        AuditLogger::log(
            action: $this->action($model, 'created'),
            subject: $model,
            newValues: $this->redact($model->getAttributes()),
        );
    }

    public function updated(Model $model): void
    {
        $changes = $this->redact($model->getChanges());

        if (empty($changes)) {
            return;
        }

        $before = [];
        foreach (array_keys($changes) as $key) {
            $before[$key] = $model->getOriginal($key);
        }

        AuditLogger::log(
            action: $this->action($model, 'updated'),
            subject: $model,
            oldValues: $before,
            newValues: $changes,
        );
    }

    public function deleted(Model $model): void
    {
        AuditLogger::log(
            action: $this->action($model, 'deleted'),
            subject: $model,
            oldValues: $this->redact($model->getOriginal()),
        );
    }

    protected function action(Model $model, string $event): string
    {
        return strtolower(class_basename($model)) . '.' . $event;
    }

    protected function redact(array $values): array
    {
        foreach (self::REDACTED as $key) {
            unset($values[$key]);
        }

        return $values;
    }
}