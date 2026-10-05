<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">كل المطاعم</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" action="{{ route('admin.restaurants.index') }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap gap-3">
                <select name="status" class="rounded border-gray-300">
                    <option value="">كل الحالات</option>
                    <option value="pending" @selected($filters['status'] === 'pending')>قيد المراجعة</option>
                    <option value="approved" @selected($filters['status'] === 'approved')>معتمدة</option>
                    <option value="rejected" @selected($filters['status'] === 'rejected')>مرفوضة</option>
                </select>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="بحث بالاسم" class="rounded border-gray-300">
                <button class="rounded bg-orange-800 px-4 py-2 text-sm font-semibold text-white">تصفية</button>
            </form>

            @foreach ($restaurants as $restaurant)
                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="font-bold">{{ $restaurant->name }}</div>
                        <div class="text-sm text-gray-600">{{ $restaurant->category->name_ar }} · {{ $restaurant->area->name_ar }} · {{ $restaurant->status->label() }}</div>
                    </div>
                    <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">عرض</a>
                </div>
            @endforeach

            {{ $restaurants->links() }}
        </div>
    </div>
</x-app-layout>
