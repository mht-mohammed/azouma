<?php

namespace App\Http\Requests\Owner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadRestaurantImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('restaurant'));
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.required' => 'اختر صورة واحدة على الأقل.',
            'images.*.image' => 'الملف يجب أن يكون صورة.',
            'images.*.mimes' => 'الصيغ المسموحة: jpg و jpeg و png و webp.',
            'images.*.max' => 'حجم الصورة يجب ألا يتجاوز 4 ميجابايت.',
        ];
    }
}
