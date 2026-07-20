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
        protected string $name,
        protected ?string $plainPassword = null,
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

        $mail = (new MailMessage)
            ->subject('Kredensial Akun Anda - ' . config('app.name'))
            ->greeting('Assalamu\'alaikum, ' . $this->name . '!')
            ->line('Akun Anda pada sistem keuangan **' . config('app.name') . '** telah dibuat oleh administrator.');

        if ($this->plainPassword !== null) {
            $mail->line('Berikut kredensial untuk masuk:')
                 ->line('**Email:** ' . $notifiable->email)
                 ->line('**Password:** ' . $this->plainPassword)
                 ->line('Demi keamanan, sebaiknya segera ganti password melalui tombol di bawah ini.')
                 ->action('Ganti Password Saya', $resetUrl)
                 ->line('Tautan ganti password berlaku selama **60 menit**. Anda tetap dapat masuk memakai password di atas kapan pun.');
        } else {
            $mail->line('Klik tombol di bawah untuk mengatur password Anda. Tautan ini berlaku selama **60 menit**.')
                 ->action('Atur Password Saya', $resetUrl)
                 ->line('Jika Anda tidak merasa mendaftar, abaikan email ini.');
        }

        return $mail->salutation('Jazakumullahu khairan,');
    }
}
