<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Maximum retry attempts.
   */
  public int $tries = 3;

  /**
   * Timeout in seconds.
   */
  public int $timeout = 120;

  public function __construct()
  {
    $this->queue = 'notifications';
  }

  /**
   * Retry delays (seconds).
   */
  public function backoff(): array
  {
    return [10, 30, 60];
  }

  /**
   * Tags for Horizon / monitoring.
   */
  public function tags(): array
  {
    return [];
  }
}
