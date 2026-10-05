<?php

namespace App\Http\Requests\Owner;

use App\Models\Restaurant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Restaurant::class);
    }

    /**
     * No status/verification fields here on purpose: owners can never
     * set them, so even injected values never reach validated().
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'price_range' => ['nullable', 'integer', 'between:1,3'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المطعم مطلوب.',
            'category_id.required' => 'اختر التصنيف.',
            'category_id.exists' => 'التصنيف المختار غير موجود.',
            'area_id.required' => 'اختر المنطقة.',
            'area_id.exists' => 'المنطقة المختارة غير موجودة.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'address.required' => 'اكتب العنوان بالتفصيل حتى يصل الزبائن إليك.',
            'price_range.between' => 'مستوى الأسعار من 1 إلى 3.',
        ];
    }
}
