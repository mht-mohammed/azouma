<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">لوحة الإدارة</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <a href="{{ route('admin.restaurants.pending') }}" class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-amber-700">{{ $pendingCount }}</div>
                    <div class="text-sm text-gray-600">قيد المراجعة</div>
                </a>
                <a href="{{ route('admin.restaurants.index', ['status' => 'approved']) }}" class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-700">{{ $approvedCount }}</div>
                    <div class="text-sm text-gray-600">معتمدة</div>
                </a>
                <a href="{{ route('admin.restaurants.index', ['status' => 'rejected']) }}" class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-700">{{ $rejectedCount }}</div>
                    <div class="text-sm text-gray-600">مرفوضة</div>
                </a>
                <a href="{{ route('admin.reports.index', ['status' => 'new']) }}" class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ $newReportsCount }}</div>
                    <div class="text-sm text-gray-600">بلاغات جديدة</div>
                </a>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-bold mb-3">اختصارات</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.restaurants.pending') }}" class="rounded bg-primary-800 px-4 py-2 text-sm font-semibold text-white">مراجعة المعلقة</a>
                    <a href="{{ route('admin.restaurants.index') }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">كل المطاعم</a>
                    <a href="{{ route('admin.reports.index') }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">البلاغات</a>
                    <a href="{{ route('admin.categories.index') }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">التصنيفات</a>
                    <a href="{{ route('admin.areas.index') }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">المناطق</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
