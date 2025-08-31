<?php

namespace App\Notifications;

use App\Models\Practice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class InviteUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Practice $practice,
        public User $inviter
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // 7‑day signed link that identifies user + practice
        $url = URL::temporarySignedRoute(
            'invite.accept',
            now()->addDays(7),
            [
                'user'     => $notifiable->getKey(),
                'practice' => $this->practice->getKey(),
            ]
        );

        return (new MailMessage)
            ->subject('You’ve been invited to ' . $this->practice->name)
            ->greeting('Welcome to ' . config('app.name'))
            ->line(($this->inviter->name ?? ($this->inviter->first_name ?? 'A teammate')) . ' has invited you to join ' . $this->practice->name . '.')
            ->action('Accept invitation', $url)
            ->line('This link lets you set your password and join the workspace. It expires in 7 days.');
    }
}
