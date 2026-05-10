<?php

namespace App\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Auto-records create/update/delete events to the activity_logs table.
 *
 * Models can override `getActivityLabel()` and `getLoggableAttributes()`
 * to control the description and which fields are tracked in updates.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            ActivityLog::record(
                event: 'created',
                subject: $model,
                description: static::getActivityModelLabel().' created',
                properties: ['attributes' => $model->getActivityLogProperties($model->getAttributes())],
            );
        });

        static::updated(function (Model $model) {
            $changes = collect($model->getChanges())
                ->except(['updated_at', 'created_at'])
                ->all();

            $tracked = $model->getLoggableAttributes();
            if (! empty($tracked)) {
                $changes = collect($changes)->only($tracked)->all();
            }

            if (empty($changes)) {
                return;
            }

            $original = collect($changes)
                ->mapWithKeys(fn ($_v, $k) => [$k => $model->getOriginal($k)])
                ->all();

            ActivityLog::record(
                event: 'updated',
                subject: $model,
                description: static::getActivityModelLabel().' updated',
                properties: [
                    'old' => $model->getActivityLogProperties($original),
                    'new' => $model->getActivityLogProperties($changes),
                ],
            );
        });

        static::deleted(function (Model $model) {
            ActivityLog::record(
                event: 'deleted',
                subject: $model,
                description: static::getActivityModelLabel().' deleted',
                properties: ['attributes' => $model->getActivityLogProperties($model->getAttributes())],
            );
        });
    }

    /**
     * Friendly label used in descriptions, e.g. "Order", "Product".
     */
    public static function getActivityModelLabel(): string
    {
        return class_basename(static::class);
    }

    /**
     * Specific attributes to track for `updated`. Empty array means "track all
     * dirty attributes except timestamps".
     *
     * @return array<int, string>
     */
    public function getLoggableAttributes(): array
    {
        return [];
    }

    /**
     * Hook to redact / cast properties before persistence.
     */
    public function getActivityLogProperties(array $attributes): array
    {
        return collect($attributes)
            ->except(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ->all();
    }
}
