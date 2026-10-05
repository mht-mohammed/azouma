@props(['at' => null])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800']) }}>
    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.7-9.3a1 1 0 00-1.4-1.4L9 10.6 7.7 9.3a1 1 0 00-1.4 1.4l2 2a1 1 0 001.4 0l4-4z" clip-rule="evenodd"/></svg>
    موثّقة
    @if ($at)
        · {{ $at->format('Y-m-d') }}
    @endif
</span>
