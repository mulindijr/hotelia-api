<?php

namespace App\Events\Services;

use App\Models\Service;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Service $service
    ) {}
}
