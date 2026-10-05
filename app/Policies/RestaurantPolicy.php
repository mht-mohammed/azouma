<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    /**
     * Found automatically by naming convention (no manual registration
     * needed): Restaurant model -> RestaurantPolicy class.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->isAdmin() || $this->owns($user, $restaurant);
    }

    /**
     * MVP: one restaurant per owner.
     */
    public function create(User $user): bool
    {
        return $user->isOwner() && ! $user->restaurants()->exists();
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->isAdmin() || $this->owns($user, $restaurant);
    }

    public function delete(User $user, Restaurant $restaurant): bool
    {
        return $user->isAdmin() || $this->owns($user, $restaurant);
    }

    private function owns(User $user, Restaurant $restaurant): bool
    {
        return $user->isOwner() && $restaurant->owner_id === $user->id;
    }
}
