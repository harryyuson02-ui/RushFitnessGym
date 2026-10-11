<?php

namespace App\Http\Requests;

use App\Models\Coach;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCoachRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['bail', 'required', 'string', 'regex:/\A09[0-9]{9}\z/'],
            'specialty' => ['required', Rule::in(Coach::SPECIALTIES)],
            'is_available' => ['nullable', 'boolean'],
            'availability' => ['required', 'array', 'size:7'],
            'availability.*' => ['required', 'array:day_of_week,enabled,starts_at,ends_at'],
            'availability.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'availability.*.enabled' => ['nullable', 'boolean'],
            'availability.*.starts_at' => ['required', 'date_format:H:i'],
            'availability.*.ends_at' => ['required', 'date_format:H:i'],
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
                $availability = $this->input('availability', []);
                $enabledSlots = collect(is_array($availability) ? $availability : [])
                    ->filter(fn (mixed $slot): bool => is_array($slot) && (bool) ($slot['enabled'] ?? false));

                foreach ($enabledSlots as $index => $slot) {
                    if (is_string($slot['starts_at'] ?? null)
                        && is_string($slot['ends_at'] ?? null)
                        && $slot['starts_at'] >= $slot['ends_at']) {
                        $validator->errors()->add("availability.{$index}.ends_at", 'The end time must be after the start time.');
                    }
                }

                if ($this->boolean('is_available') && $enabledSlots->isEmpty()) {
                    $validator->errors()->add('availability', 'Select at least one available day.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.required' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
            'phone.string' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
            'phone.regex' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
        ];
    }
}
