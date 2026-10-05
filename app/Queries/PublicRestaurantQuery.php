<?php

namespace App\Queries;

use App\Models\Restaurant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PublicRestaurantQuery
{
    /**
     * Approved restaurants with list filters, eager loaded for cards.
     *
     * @param  array{category?: int, area?: int, q?: string}  $filters
     */
    public function __invoke(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return Restaurant::query()
            ->approved()
            ->search($filters['q'] ?? null)
            ->when(
                isset($filters['category']),
                fn ($query) => $query->inCategory((int) $filters['category'])
            )
            ->when(
                isset($filters['area']),
                fn ($query) => $query->inArea((int) $filters['area'])
            )
            ->with(['category', 'area', 'coverImage'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
