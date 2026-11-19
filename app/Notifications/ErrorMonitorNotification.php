<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ErrorMonitorNotification extends Notification
{
    use Queueable;

    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'event_id' => $this->payload['event_id'],
            'exception_class' => $this->payload['exception_class'],
            'message' => $this->payload['message'],
            'file' => $this->payload['file'],
            'line' => $this->payload['line'],
            'url' => $this->payload['url'],
            'occurrences' => $this->payload['occurrences'],
            'last_seen_at' => $this->payload['last_seen_at'],
            'type' => 'dev_error',
        ];
    }
}
