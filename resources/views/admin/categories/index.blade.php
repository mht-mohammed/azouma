<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">التصنيفات</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 text-red-800 rounded-lg p-4">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.categories.store') }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-3">
                @csrf
                <label class="block flex-1">
                    <span class="text-sm text-gray-600">تصنيف جديد</span>
                    <input type="text" name="name_ar" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                </label>
                <x-primary-button>إضافة</x-primary-button>
            </form>

            @foreach ($categories as $category)
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PUT')
                    <label class="block flex-1">
                        <span class="text-sm text-gray-600">{{ $category->restaurants_count }} مطعم</span>
                        <input type="text" name="name_ar" value="{{ $category->name_ar }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </label>
                    <x-secondary-button>حفظ</x-secondary-button>
                </form>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('حذف التصنيف؟')" class="-mt-2 text-end">
                    @csrf
                    @method('DELETE')
                    <button class="text-xs text-red-600 underline">حذف</button>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
