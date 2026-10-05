@extends('layouts.public')

@section('title', $restaurant->name.' — عزومة')
@section('meta_description', $restaurant->name.' — '.$restaurant->category->name_ar.' في '.$restaurant->area->name_ar.'. '.$restaurant->operating_status->label().'.')

@section('content')
    <a href="{{ route('home') }}" class="mb-4 inline-block text-sm text-stone-600 underline">→ كل المطاعم</a>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        @if ($restaurant->images->isNotEmpty())
            <img src="{{ $restaurant->images->first()->url }}" alt="صورة {{ $restaurant->name }}" class="h-64 w-full object-cover sm:h-80">
            @if ($restaurant->images->count() > 1)
                <div class="grid grid-cols-3 gap-2 p-3">
                    @foreach ($restaurant->images->skip(1) as $image)
                        <img src="{{ $image->url }}" alt="صورة {{ $restaurant->name }}" loading="lazy" class="h-24 w-full rounded object-cover">
                    @endforeach
                </div>
            @endif
        @else
            <img src="{{ asset('images/placeholder-restaurant.svg') }}" alt="لا توجد صور بعد" class="h-64 w-full object-cover">
        @endif

        <div class="p-4 sm:p-6">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-bold">{{ $restaurant->name }}</h1>
                @if ($restaurant->is_verified)
                    <x-verified-badge :at="$restaurant->last_verified_at" />
                @endif
            </div>
            <p class="mt-1 text-stone-600">{{ $restaurant->category->name_ar }} · {{ $restaurant->area->name_ar }}</p>

            <div class="mt-4 rounded-lg bg-amber-100 p-3 text-sm">
                <x-status-badge :status="$restaurant->operating_status" />
                @if ($isOpenNow)
                    <span class="ms-2 font-semibold text-green-800">· مفتوح الآن</span>
                @else
                    <span class="ms-2 text-stone-600">· مغلق حالياً</span>
                @endif
                <span class="block mt-1 text-stone-600">آخر تحديث للحالة: {{ $restaurant->statusUpdatedAt()->format('Y-m-d') }}</span>
            </div>

            @if ($restaurant->description)
                <p class="mt-4 leading-relaxed">{{ $restaurant->description }}</p>
            @endif

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex gap-2">
                    <dt class="font-semibold">العنوان التفصيلي:</dt>
                    <dd>{{ $restaurant->address ?? '—' }}</dd>
                </div>
                @if ($restaurant->price_range)
                    <div class="flex gap-2">
                        <dt class="font-semibold">الأسعار:</dt>
                        <dd>{{ str_repeat('₪', $restaurant->price_range) }} ({{ $restaurant->price_range }} من 3)</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                @if ($restaurant->phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $restaurant->phone) }}" class="rounded bg-orange-800 px-4 py-2 text-sm font-semibold text-white">اتصال</a>
                @endif
                @if ($restaurant->whatsappUrl())
                    <a href="{{ $restaurant->whatsappUrl() }}" class="rounded bg-green-700 px-4 py-2 text-sm font-semibold text-white">واتساب</a>
                @endif
            </div>

            <h2 class="mt-8 text-lg font-bold">ساعات الدوام</h2>            <table class="mt-2 w-full text-sm">
                <tbody>
                    @foreach ($restaurant->openingHours as $hour)
                        <tr class="{{ $hour->day_of_week->value === $today ? 'bg-amber-100 font-semibold' : '' }}">
                            <td class="border-b py-2">{{ $hour->day_of_week->label() }}</td>
                            <td class="border-b py-2 text-end">
                                @if ($hour->is_closed)
                                    مغلق
                                @else
                                    {{ substr($hour->opens_at, 0, 5) }} – {{ substr($hour->closes_at, 0, 5) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="mt-8 text-lg font-bold">الإبلاغ عن معلومات خاطئة</h2>
            @if (session('success'))
                <p class="mt-2 rounded bg-green-100 p-3 text-sm text-green-800">{{ session('success') }}</p>
            @endif
            <form method="POST" action="{{ route('restaurants.reports.store', $restaurant) }}" class="mt-2 space-y-3">
                @csrf
                <label class="block">
                    <span class="text-sm text-stone-600">السبب *</span>
                    <select name="reason" class="mt-1 block w-full rounded border-stone-300" required>
                        <option value="">اختر…</option>
                        @foreach (\App\Enums\ReportReason::cases() as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm text-stone-600">ما المشكلة؟ *</span>
                    <textarea name="message" rows="3" class="mt-1 block w-full rounded border-stone-300" required>{{ old('message') }}</textarea>
                </label>
                <label class="block">
                    <span class="text-sm text-stone-600">وسيلة تواصل (اختياري)</span>
                    <input type="text" name="reporter_contact" value="{{ old('reporter_contact') }}" class="mt-1 block w-full rounded border-stone-300">
                </label>
                <input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                <button class="rounded bg-stone-700 px-4 py-2 text-sm font-semibold text-white">إرسال البلاغ</button>
            </form>

        </div>
    </div>
@endsection
