<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">مطاعم قيد المراجعة</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            @forelse ($restaurants as $restaurant)
                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="font-bold">{{ $restaurant->name }}</div>
                        <div class="text-sm text-gray-600">{{ $restaurant->category->name_ar }} · {{ $restaurant->area->name_ar }} · المالك: {{ $restaurant->owner->name }}</div>
                    </div>
                    <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="rounded bg-orange-800 px-4 py-2 text-sm font-semibold text-white">مراجعة</a>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center text-gray-600">لا توجد مطاعم قيد المراجعة.</div>
            @endforelse

            {{ $restaurants->links() }}
        </div>
    </div>
</x-app-layout>
