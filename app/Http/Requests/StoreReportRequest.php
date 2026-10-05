<?php

namespace App\Http\Requests;

use App\Enums\ReportReason;
use App\Enums\RestaurantStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Reports are only accepted for publicly visible restaurants.
        return $this->route('restaurant')->status === RestaurantStatus::APPROVED;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $reasons = implode(',', array_column(ReportReason::cases(), 'value'));

        return [
            'reason' => ['required', 'in:'.$reasons],
            'message' => ['required', 'string', 'max:2000'],
            'reporter_contact' => ['nullable', 'string', 'max:100'],
            // Honeypot: real users leave it empty, bots fill it in.
            'website' => ['nullable', 'prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'اختر سبب البلاغ.',
            'reason.in' => 'سبب البلاغ غير صالح.',
            'message.required' => 'اكتب وصفاً للمشكلة.',
        ];
    }
}
