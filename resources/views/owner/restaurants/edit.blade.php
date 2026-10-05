<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">تعديل: {{ $restaurant->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('owner.restaurants.update', $restaurant) }}" class="p-6">
                    @csrf
                    @method('PUT')
                    <h3 class="mb-4 font-bold">بيانات المطعم</h3>
                    @include('owner.restaurants.partials.fields')
                    <div class="mt-6">
                        <x-primary-button>حفظ البيانات</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('owner.restaurants.hours', $restaurant) }}" class="p-6">
                    @csrf
                    @method('PUT')
                    <h3 class="mb-1 font-bold">ساعات الدوام</h3>
                    <p class="mb-4 text-sm text-gray-600">إغلاق أبكر من الفتح يعني أن الدوام يستمر بعد منتصف الليل.</p>
                    <div class="space-y-3">
                        @foreach ($weekdays as $day)
                            @php $row = $hoursByDay->get($day->value); @endphp
                            <div class="grid grid-cols-2 gap-2 items-end sm:grid-cols-4">
                                <span class="font-semibold">{{ $day->label() }}</span>
                                <input type="hidden" name="hours[{{ $day->value }}][day_of_week]" value="{{ $day->value }}">
                                <input type="hidden" name="hours[{{ $day->value }}][is_closed]" value="0">
                                <label class="block">
                                    <span class="text-xs text-gray-600">من</span>
                                    <input type="time" name="hours[{{ $day->value }}][opens_at]" value="{{ $row && ! $row->is_closed ? substr($row->opens_at, 0, 5) : '' }}" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </label>
                                <label class="block">
                                    <span class="text-xs text-gray-600">إلى</span>
                                    <input type="time" name="hours[{{ $day->value }}][closes_at]" value="{{ $row && ! $row->is_closed ? substr($row->closes_at, 0, 5) : '' }}" class="block w-full rounded-md border-gray-300 shadow-sm">
                                </label>
                                <label class="flex items-center gap-1 text-sm">
                                    <input type="checkbox" name="hours[{{ $day->value }}][is_closed]" value="1" @checked($row && $row->is_closed) class="rounded border-gray-300">
                                    مغلق
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('hours')" class="mt-2" />
                    <div class="mt-4">
                        <x-primary-button>حفظ الساعات</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-1 font-bold">الصور ({{ $restaurant->images->count() }} من 10)</h3>
                    <form method="POST" action="{{ route('owner.restaurants.images.store', $restaurant) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        <label class="block">
                            <span class="text-sm text-gray-600">صور جديدة (jpg/png/webp حتى 4MB)</span>
                            <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp" class="mt-1 block text-sm">
                        </label>
                        <x-primary-button>رفع</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('images')" class="mt-2" />
                    <x-input-error :messages="$errors->get('images.*')" class="mt-2" />

                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($restaurant->images as $image)
                            <div class="rounded border p-2">
                                <img src="{{ $image->url }}" alt="صورة المطعم" loading="lazy" class="h-24 w-full object-cover rounded">
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @if ($image->is_cover)
                                        <span class="text-xs font-semibold text-teal-700">الغلاف</span>
                                    @else
                                        <form method="POST" action="{{ route('owner.restaurants.images.cover', [$restaurant, $image]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="text-xs underline">اجعلها الغلاف</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('owner.restaurants.images.move', [$restaurant, $image]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="direction" value="up">
                                        <button class="text-xs underline">↑</button>
                                    </form>
                                    <form method="POST" action="{{ route('owner.restaurants.images.move', [$restaurant, $image]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="direction" value="down">
                                        <button class="text-xs underline">↓</button>
                                    </form>
                                    <form method="POST" action="{{ route('owner.restaurants.images.destroy', [$restaurant, $image]) }}" onsubmit="return confirm('حذف الصورة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-600 underline">حذف</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
