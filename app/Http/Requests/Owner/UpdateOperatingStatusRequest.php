<?php

namespace App\Http\Requests\Owner;

use App\Enums\OperatingStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOperatingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('restaurant'));
    }

    /**
     * Values come from the enum so they stay in one place.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $values = implode(',', array_column(OperatingStatus::cases(), 'value'));

        return [
            'operating_status' => ['required', 'in:'.$values],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'operating_status.required' => 'اختر حالة الدوام.',
            'operating_status.in' => 'حالة الدوام المختارة غير صالحة.',
        ];
    }
}
