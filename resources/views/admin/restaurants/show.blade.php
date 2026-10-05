<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">مراجعة: {{ $restaurant->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6 space-y-3 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-bold text-base">{{ $restaurant->name }}</span>
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ $restaurant->status->label() }}</span>
                    @if ($restaurant->is_verified)
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-800">موثّقة</span>
                    @endif
                </div>
                <p><strong>المالك:</strong> {{ $restaurant->owner->name }} ({{ $restaurant->owner->email }})</p>
                <p><strong>التصنيف / المنطقة:</strong> {{ $restaurant->category->name_ar }} / {{ $restaurant->area->name_ar }}</p>
                <p><strong>العنوان التفصيلي:</strong> {{ $restaurant->address ?? '—' }}</p>
                <p><strong>الهاتف:</strong> <span dir="ltr">{{ $restaurant->phone ?? '—' }}</span> · <strong>واتساب:</strong> <span dir="ltr">{{ $restaurant->whatsapp ?? '—' }}</span></p>
                <p><strong>الوصف:</strong> {{ $restaurant->description ?? '—' }}</p>
                <p><strong>حالة الدوام:</strong> {{ $restaurant->operating_status->label() }}</p>
                @if ($restaurant->status->value === 'rejected' && $restaurant->rejection_reason)
                    <p class="text-red-700"><strong>سبب الرفض:</strong> {{ $restaurant->rejection_reason }}</p>
                @endif

                <div>
                    <strong>الساعات:</strong>
                    <ul class="mt-1 space-y-1">
                        @foreach ($restaurant->openingHours as $hour)
                            <li>{{ $hour->day_of_week->label() }}: {{ $hour->is_closed ? 'مغلق' : substr($hour->opens_at, 0, 5).' – '.substr($hour->closes_at, 0, 5) }}</li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <strong>الصور ({{ $restaurant->images->count() }}):</strong>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach ($restaurant->images as $image)
                            <img src="{{ $image->url }}" alt="" loading="lazy" class="h-20 w-full object-cover rounded {{ $image->is_cover ? 'ring-2 ring-teal-600' : '' }}">
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 space-y-4">
                <form method="POST" action="{{ route('admin.restaurants.approve', $restaurant) }}">
                    @csrf
                    <x-primary-button>اعتماد</x-primary-button>
                </form>

                <form method="POST" action="{{ route('admin.restaurants.reject', $restaurant) }}" class="space-y-2">
                    @csrf
                    <label class="block">
                        <span class="text-sm text-gray-600">سبب الرفض *</span>
                        <textarea name="rejection_reason" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                    </label>
                    <x-input-error :messages="$errors->get('rejection_reason')" />
                    <x-danger-button>رفض</x-danger-button>
                </form>

                <div class="flex gap-2">
                    @if (! $restaurant->is_verified)
                        <form method="POST" action="{{ route('admin.restaurants.verify', $restaurant) }}">
                            @csrf
                            <x-secondary-button>توثيق</x-secondary-button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.restaurants.unverify', $restaurant) }}">
                            @csrf
                            <x-secondary-button>إزالة التوثيق</x-secondary-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
