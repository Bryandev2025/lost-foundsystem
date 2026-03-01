<?php

namespace App\Services\Audit;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        ?int $actorId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $meta = []
    ): void {
        ActivityLog::create([
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta' => $meta,
        ]);
    }
}