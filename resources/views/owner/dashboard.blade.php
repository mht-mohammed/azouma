<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">لوحة صاحب المطعم</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            @if (! $restaurant)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="text-lg font-semibold">لا يوجد لديك مطعم بعد</p>
                        <p class="mt-2 text-gray-600">أنشئ مطعمك ليتم مراجعته واعتماده.</p>
                        <a href="{{ route('owner.restaurants.create') }}" class="mt-4 inline-block rounded bg-orange-800 px-4 py-2 text-sm font-semibold text-white">إنشاء مطعم</a>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-lg font-bold">{{ $restaurant->name }}</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                {{ $restaurant->status->value === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $restaurant->status->value === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $restaurant->status->value === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $restaurant->status->label() }}
                            </span>
                        </div>

                        @if ($restaurant->status->value === 'rejected' && $restaurant->rejection_reason)
                            <p class="mt-2 text-sm text-red-700">سبب الرفض: {{ $restaurant->rejection_reason }}</p>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('owner.restaurants.edit', $restaurant) }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">تعديل البيانات والساعات والصور</a>
                            @if ($restaurant->status->value === 'approved')
                                <a href="{{ route('restaurants.show', $restaurant->slug) }}" class="rounded px-4 py-2 text-sm text-stone-600 underline">عرض الصفحة العامة</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-bold">حالة الدوام الحالية: {{ $restaurant->operating_status->label() }}</h3>
                        <form method="POST" action="{{ route('owner.restaurants.status', $restaurant) }}" class="mt-3 flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PATCH')
                            <label class="block">
                                <span class="text-sm text-gray-600">تغيير سريع للحالة</span>
                                <select name="operating_status" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                                    @foreach (\App\Enums\OperatingStatus::cases() as $case)
                                        <option value="{{ $case->value }}" @selected($restaurant->operating_status === $case)>{{ $case->label() }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <x-primary-button>حفظ الحالة</x-primary-button>
                        </form>
                        <x-input-error :messages="$errors->get('operating_status')" class="mt-2" />
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
