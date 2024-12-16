<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceivingNotification extends Notification
{
    use Queueable;

    protected $receiving;

    /**
     * Create a new notification instance.
     *
     * @param  object  $payable
     * @return void
     */
    public function __construct($receiving)
    {
        $this->receiving = $receiving;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->receiving->id,
            'code' => $this->receiving->code,
            'created_at' => $this->receiving->created_at,
            'status' => $this->receiving->status, // Added status field
        ];
    }
}
