<?php

namespace App\Events\Housekeeping;

use App\Models\HousekeepingTask;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HousekeepingTaskCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public HousekeepingTask $task
    ) {}
}
