<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoyaltyPointsEarnedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $pointsEarned,
        public int $totalPoints,
        public string $tier,
        public string $orderNumber
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'loyalty_points_earned',
            'points_earned' => $this->pointsEarned,
            'total_points' => $this->totalPoints,
            'tier' => $this->tier,
            'order_number' => $this->orderNumber,
            'message' => "Vous avez gagné {$this->pointsEarned} points pour votre commande #{$this->orderNumber}!",
        ];
    }
}
