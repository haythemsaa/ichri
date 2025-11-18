<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TeamInvitation $invitation
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/team/accept-invitation/' . $this->invitation->token);

        return (new MailMessage)
            ->subject('Invitation à rejoindre une équipe sur ichri.tn')
            ->greeting('Bonjour!')
            ->line('Vous avez été invité à rejoindre une équipe sur ichri.tn.')
            ->line('Rôle: ' . $this->invitation->role)
            ->action('Accepter l\'invitation', $url)
            ->line('Cette invitation expire le ' . $this->invitation->expires_at->format('d/m/Y'))
            ->line('Merci d\'utiliser ichri.tn!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'role' => $this->invitation->role,
            'invited_by' => $this->invitation->invitedBy->store_name ?? 'Unknown',
            'expires_at' => $this->invitation->expires_at,
        ];
    }
}
