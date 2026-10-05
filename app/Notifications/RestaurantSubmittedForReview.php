<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RestaurantSubmittedForReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Restaurant $restaurant) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'restaurant_submitted',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
        ];
    }
}
