@props(['restaurant'])

<a href="{{ route('restaurants.show', $restaurant) }}" class="block overflow-hidden rounded-lg bg-white shadow-sm transition hover:shadow-md">
    <img src="{{ $restaurant->coverUrl() }}" alt="صورة {{ $restaurant->name }}" loading="lazy" class="h-44 w-full object-cover">
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <h2 class="font-bold leading-snug">{{ $restaurant->name }}</h2>
            @if ($restaurant->is_verified)
                <x-verified-badge />
            @endif
        </div>
        <p class="mt-1 text-sm text-stone-600">{{ $restaurant->category->name_ar }} · {{ $restaurant->area->name_ar }}</p>
        <div class="mt-3">
            <x-status-badge :status="$restaurant->operating_status" />
        </div>
    </div>
</a>
