<?php

namespace App\Notifications;

use App\Models\PermitApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PermitExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PermitApplication $application) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your practice permit expires soon')
            ->line("Your permit {$this->application->permit_number} for {$this->application->practice_name} expires on ".$this->application->expires_on?->toFormattedDateString().'.')
            ->line('Renew your professional license and submit a new application before then to keep practising.')
            ->action('Open your applications', route('applications.index'));
    }
}
