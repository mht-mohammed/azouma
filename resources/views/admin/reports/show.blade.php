<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">بلاغ عن: {{ $report->restaurant->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-2 text-sm">
                <p><strong>السبب:</strong> {{ $report->reason->label() }}</p>
                <p><strong>الرسالة:</strong> {{ $report->message }}</p>
                <p><strong>للتواصل:</strong> {{ $report->reporter_contact ?? '—' }}</p>
                <p><strong>الحالة:</strong> {{ $report->status->label() }} · <strong>التاريخ:</strong> {{ $report->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>المطعم:</strong> {{ $report->restaurant->name }} ({{ $report->restaurant->owner->name }})</p>
            </div>

            @if ($report->status->value === 'new')
                <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                    @csrf
                    <x-primary-button>وضع كمعالَج</x-primary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
