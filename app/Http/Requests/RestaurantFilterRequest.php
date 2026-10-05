<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RestaurantFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'area' => ['nullable', 'integer', 'exists:areas,id'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
