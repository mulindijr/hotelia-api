<?php

namespace App\Events\Services;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Service $service
    ) {}
}
