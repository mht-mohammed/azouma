<?php

namespace App\Http\Requests\Owner;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOpeningHoursRequest extends FormRequest
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
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'hours.*.is_closed' => ['sometimes', 'boolean'],
            'hours.*.opens_at' => ['required_if:hours.*.is_closed,false', 'nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['required_if:hours.*.is_closed,false', 'nullable', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hours.required' => 'أدخل ساعات الدوام لأيام الأسبوع.',
            'hours.size' => 'يجب إدخال ساعات الأيام السبعة.',
            'hours.*.opens_at.required_if' => 'أدخل وقت الفتح لليوم المفتوح.',
            'hours.*.closes_at.required_if' => 'أدخل وقت الإغلاق لليوم المفتوح.',
            'hours.*.opens_at.date_format' => 'صيغة الوقت غير صالحة (مثال: 09:00).',
            'hours.*.closes_at.date_format' => 'صيغة الوقت غير صالحة (مثال: 23:00).',
        ];
    }
}
