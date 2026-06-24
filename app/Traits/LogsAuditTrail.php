<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsAuditTrail
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        // Set the log name to the lowercase class name of the model
        $logName = strtolower(class_basename($this));

        return LogOptions::defaults()
            ->useLogName($logName)
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}