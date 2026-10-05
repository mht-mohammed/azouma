@props(['title', 'subtitle' => null])

<div class="mb-6">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-1 text-stone-600">{{ $subtitle }}</p>
    @endif
</div>
