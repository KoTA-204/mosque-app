<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class AkunDibuatNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $name
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Akun Anda Telah Dibuat – ' . config('app.name'))
            ->greeting('Assalamu\'alaikum, ' . $this->name . '!')
            ->line('Akun Anda di sistem keuangan **' . config('app.name') . '** telah dibuat oleh administrator.')
            ->line('Klik tombol di bawah untuk mengatur password Anda. Tautan ini berlaku selama **60 menit**.')
            ->action('Atur Password Saya', $resetUrl)
            ->line('Jika Anda tidak merasa mendaftar, abaikan email ini.')
            ->salutation('Jazakumullahu khairan,');
    }
}