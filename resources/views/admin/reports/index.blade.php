<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">البلاغات</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 rounded-lg p-4">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.reports.index') }}" class="bg-white shadow-sm rounded-lg p-4 flex gap-3">
                <select name="status" class="rounded border-gray-300" onchange="this.form.submit()">
                    <option value="">كل البلاغات</option>
                    <option value="new" @selected($filters['status'] === 'new')>جديدة</option>
                    <option value="resolved" @selected($filters['status'] === 'resolved')>معالَجة</option>
                </select>
            </form>

            @forelse ($reports as $report)
                <div class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="font-bold">{{ $report->reason->label() }} — {{ $report->restaurant->name }}</div>
                        <div class="text-sm text-gray-600">{{ $report->status->label() }} · {{ $report->created_at->format('Y-m-d') }}</div>
                    </div>
                    <a href="{{ route('admin.reports.show', $report) }}" class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">عرض</a>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center text-gray-600">لا توجد بلاغات.</div>
            @endforelse

            {{ $reports->links() }}
        </div>
    </div>
</x-app-layout>
