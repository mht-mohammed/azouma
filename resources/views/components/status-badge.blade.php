@props(['status'])

@php
    $classes = match ($status->value) {
        'open' => 'bg-green-100 text-green-800',
        'temporarily_closed' => 'bg-red-100 text-red-800',
        'relocated' => 'bg-blue-100 text-blue-800',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-block rounded-full px-3 py-1 text-xs font-semibold '.$classes]) }}>
    {{ $status->label() }}
</span>
