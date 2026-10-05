@props(['title', 'message' => null])

<div class="rounded-lg bg-white p-8 text-center shadow-sm">
    <p class="text-lg font-semibold">{{ $title }}</p>
    @if ($message)
        <p class="mt-2 text-stone-600">{{ $message }}</p>
    @endif
</div>
