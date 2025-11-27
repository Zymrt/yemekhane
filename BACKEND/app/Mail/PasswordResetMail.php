<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token; // Kodu burada tutacağız

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Şifre Sıfırlama Kodunuz') // Konu başlığı
                    ->view('emails.password_reset');     // Görünüm dosyası
    }
}