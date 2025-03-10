<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class CustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $messageBody;
    public $user;

    public function __construct($subject, $messageBody)
    {
        $this->subject = $subject;
        $this->messageBody = $messageBody;
        $this->user = Auth::user();
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.custom') // Ubah ke view email yang benar
                    ->with([
                        'messageBody' => $this->messageBody,
                        'user' => $this->user, // Kirim data user ke view
                    ]);
    }
}