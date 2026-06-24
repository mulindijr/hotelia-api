<?php

namespace App\Traits;

use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\HasActivity;

trait LogsAuditTrail
{
    use HasActivity;

    public function getActivitylogOptions(): LogOptions
    {
        // Set the log name to the lowercase class name of the model
        $logName = strtolower(class_basename($this));

        return LogOptions::defaults()
            ->useLogName($logName)
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
