<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">التنبيهات</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @forelse ($notifications as $notification)
                <div class="bg-white shadow-sm rounded-lg p-4 flex items-center justify-between gap-3 {{ is_null($notification->read_at) ? 'border-r-4 border-orange-600' : '' }}">
                    <div class="text-sm">
                        @if (($notification->data['type'] ?? '') === 'restaurant_approved')
                            تم اعتماد مطعمك "{{ $notification->data['restaurant_name'] ?? '' }}".
                        @elseif (($notification->data['type'] ?? '') === 'restaurant_rejected')
                            لم يُعتمد مطعمك "{{ $notification->data['restaurant_name'] ?? '' }}".
                            @if (! empty($notification->data['rejection_reason']))
                                السبب: {{ $notification->data['rejection_reason'] }}
                            @endif
                        @else
                            تنبيه جديد.
                        @endif
                        <div class="text-xs text-gray-500">{{ $notification->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    @if (is_null($notification->read_at))
                        <form method="POST" action="{{ route('owner.notifications.read', $notification) }}">
                            @csrf
                            @method('PATCH')
                            <button class="text-xs underline">تعليم كمقروء</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center text-gray-600">لا توجد تنبيهات.</div>
            @endforelse

            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
