<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InternalRevisionOtpNotification extends Notification
{
    use Queueable;

    protected $revision;
    protected $otp;

    public function __construct($revision, $otp)
    {
        $this->revision = $revision;
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'internal_revision_otp',
            'revision_id' => $this->revision->id,
            'do_id' => $this->revision->do_id,
            'otp' => $this->otp,
            'message' => 'Kode OTP approval Revisi Internal DO: ' . $this->otp . ' (berlaku 5 menit)',
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}