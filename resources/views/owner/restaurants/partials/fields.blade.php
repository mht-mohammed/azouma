@php
    $isEdit = isset($restaurant);
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="اسم المطعم *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $restaurant->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="phone" value="رقم الهاتف *" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" dir="ltr" :value="old('phone', $restaurant->phone ?? '')" required />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="category_id" value="التصنيف *" />
        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="">اختر…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $restaurant->category_id ?? '') === (string) $category->id)>{{ $category->name_ar }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="area_id" value="المنطقة *" />
        <select id="area_id" name="area_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="">اختر…</option>
            @foreach ($areas as $area)
                <option value="{{ $area->id }}" @selected((string) old('area_id', $restaurant->area_id ?? '') === (string) $area->id)>{{ $area->name_ar }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="address" value="العنوان بالتفصيل * (الحي، الشارع، أقرب علامة — هذا ما يصل به الزبائن إليك)" />
    <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('address', $restaurant->address ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" value="وصف المطعم" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $restaurant->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-4">
    <div>
        <x-input-label for="whatsapp" value="واتساب" />
        <x-text-input id="whatsapp" name="whatsapp" type="text" class="mt-1 block w-full" dir="ltr" :value="old('whatsapp', $restaurant->whatsapp ?? '')" />
        <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="price_range" value="مستوى الأسعار" />
        <select id="price_range" name="price_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">بدون تحديد</option>
            <option value="1" @selected((string) old('price_range', $restaurant->price_range ?? '') === '1')>1 — اقتصادي</option>
            <option value="2" @selected((string) old('price_range', $restaurant->price_range ?? '') === '2')>2 — متوسط</option>
            <option value="3" @selected((string) old('price_range', $restaurant->price_range ?? '') === '3')>3 — مرتفع</option>
        </select>
        <x-input-error :messages="$errors->get('price_range')" class="mt-2" />
    </div>
</div>

@if ($isEdit)
    <p class="mt-4 text-sm text-amber-800">تنبيه: تغيير الاسم أو التصنيف أو المنطقة أو الهاتف يعيد المطعم إلى قيد المراجعة.</p>
@endif
