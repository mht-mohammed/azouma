@extends('layouts.public')

@section('title', 'عزومة — دليل مطاعم غزة')

@section('content')
    <x-page-header title="مطاعم غزة" subtitle="تصفح المطاعم المعتمدة: الصور، حالة الدوام، والساعات" />

    <form method="GET" action="{{ route('home') }}" class="mb-6 grid grid-cols-1 gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-4">
        <label class="block">
            <span class="mb-1 block text-sm text-stone-600">التصنيف</span>
            <select name="category" class="w-full rounded border-stone-300">
                <option value="">كل التصنيفات</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>{{ $category->name_ar }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-sm text-stone-600">المنطقة</span>
            <select name="area" class="w-full rounded border-stone-300">
                <option value="">كل المناطق</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}" @selected((string) $filters['area'] === (string) $area->id)>{{ $area->name_ar }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-sm text-stone-600">بحث بالاسم</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="مثال: مشاوي" class="w-full rounded border-stone-300">
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded bg-primary-800 px-4 py-2 text-sm font-semibold text-white">تصفية</button>
            <a href="{{ route('home') }}" class="rounded px-3 py-2 text-sm text-stone-600 underline">مسح</a>
        </div>
    </form>

    @if ($restaurants->isEmpty())
        <x-empty-state title="لا توجد نتائج مطابقة" message="جرّب تصفية مختلفة أو امسح البحث لعرض كل المطاعم." />
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($restaurants as $restaurant)
                <x-restaurant-card :restaurant="$restaurant" />
            @endforeach
        </div>
        <div class="mt-6">
            {{ $restaurants->links() }}
        </div>
    @endif
@endsection
