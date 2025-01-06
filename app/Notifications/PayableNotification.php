<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayableNotification extends Notification
{
    use Queueable;

    protected $payable;

    /**
     * Create a new notification instance.
     *
     * @param  object  $payable
     * @return void
     */
    public function __construct($payable)
    {
        $this->payable = $payable;
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
            'id' => $this->payable->id,
            'code' => $this->payable->code,
            'customer' => $this->payable->customer->name,
            'customer_kota' => $this->payable->customer->text_kota,
            'created_at' => $this->payable->created_at,
            'status' => $this->payable->status, // Added status field
        ];
    }
}
