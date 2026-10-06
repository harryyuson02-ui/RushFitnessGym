<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCoachAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isCoach() && $this->user()->coach !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_available' => ['nullable', 'boolean'],
            'slots' => ['required', 'array', 'size:7'],
            'slots.*' => ['required', 'array:day_of_week,enabled,starts_at,ends_at'],
            'slots.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'slots.*.enabled' => ['nullable', 'boolean'],
            'slots.*.starts_at' => ['required', 'date_format:H:i'],
            'slots.*.ends_at' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * Get the after validation callables for the request.
     *
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $slots = $this->input('slots', []);
                $enabledSlots = collect(is_array($slots) ? $slots : [])
                    ->filter(fn (mixed $slot): bool => is_array($slot) && (bool) ($slot['enabled'] ?? false));

                foreach ($enabledSlots as $index => $slot) {
                    if (is_string($slot['starts_at'] ?? null)
                        && is_string($slot['ends_at'] ?? null)
                        && $slot['starts_at'] >= $slot['ends_at']) {
                        $validator->errors()->add("slots.{$index}.ends_at", 'The end time must be after the start time.');
                    }
                }

                if ($this->boolean('is_available') && $enabledSlots->isEmpty()) {
                    $validator->errors()->add('slots', 'Select at least one available day.');
                }
            },
        ];
    }
}
