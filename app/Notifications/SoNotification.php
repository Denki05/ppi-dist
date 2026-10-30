<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SoNotification extends Notification
{
    use Queueable;

    public $so;
    public $kind;

    /**
     * @param mixed  $so   SalesOrder
     * @param string $kind lanjutan | proforma
     */
    public function __construct($so, $kind = 'lanjutan')
    {
        $this->so = $so;
        $this->kind = $kind;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $member = $this->so->member;

        return [
            'id' => $this->so->id,
            'code' => $this->so->so_code,
            'customer' => $member ? $member->name : null,
            'customer_kota' => $member ? $member->text_kota : null,
            'type_transaction' => $this->so->type_transaction,
            'kind' => $this->kind,
            'created_at' => $this->so->created_at,
        ];
    }
}
