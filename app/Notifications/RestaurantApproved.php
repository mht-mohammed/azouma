<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestaurantApproved extends Notification implements ShouldQueue
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
            ->subject('تم اعتماد مطعمك في عزومة')
            ->line('مطعم "'.$this->restaurant->name.'" أصبح معتمداً وظاهراً للزوار.')
            ->action('عرض المطعم', route('restaurants.show', $this->restaurant->slug));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'restaurant_approved',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
        ];
    }
}
