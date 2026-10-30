<?php

namespace App\Notifications;

use App\Models\PermitApplication;
use App\PermitStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PermitDecided extends Notification implements ShouldQueue
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
        $application = $this->application;
        $message = (new MailMessage)->subject("Your permit application for {$application->practice_name}");

        if ($application->status === PermitStatus::Approved) {
            return $message
                ->line("Your permit for {$application->practice_name} has been approved.")
                ->line("Permit number: {$application->permit_number}")
                ->line('Valid until: '.$application->expires_on?->toFormattedDateString())
                ->action('Print your permit', route('permits.print', $application));
        }

        return $message
            ->line("Your permit application for {$application->practice_name} was not approved.")
            ->line("Reason: {$application->rejection_reason}")
            ->action('View the application', route('applications.show', $application));
    }
}
