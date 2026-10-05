<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestaurantRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Restaurant $restaurant) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('مطعمك في عزومة يحتاج تعديلاً')
            ->line('مطعم "'.$this->restaurant->name.'" لم يُعتمد. السبب:')
            ->line($this->restaurant->rejection_reason ?? '—')
            ->action('تعديل المطعم', route('owner.restaurants.edit', $this->restaurant));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'restaurant_rejected',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'rejection_reason' => $this->restaurant->rejection_reason,
        ];
    }
}
