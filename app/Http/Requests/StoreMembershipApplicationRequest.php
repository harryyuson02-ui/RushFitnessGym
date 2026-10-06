<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isMember() && $this->user()->member === null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fitness_goal' => ['required', Rule::in(['Weight loss', 'Build muscle', 'Improve strength', 'Improve endurance', 'General fitness', 'Mobility and flexibility'])],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['Female', 'Male', 'Non-binary', 'Prefer not to say'])],
            'phone_country_code' => ['required', Rule::in(['+63'])],
            'phone' => ['bail', 'required', 'string', 'regex:/\A09[0-9]{9}\z/'],
            'address' => ['required', Rule::in(['Tagum City', 'Davao City', 'Panabo City', 'Samal City', 'Maco', 'Mabini', 'Pantukan'])],
            'preferred_channel' => ['required', Rule::in(['private_message', 'sms'])],
            'availability_days' => ['required', 'array', 'min:1'],
            'availability_days.*' => ['required', 'distinct', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'availability_starts_at' => ['required', 'date_format:H:i'],
            'availability_ends_at' => ['required', 'date_format:H:i', 'after:availability_starts_at'],
            'coach_id' => ['nullable', Rule::exists('coaches', 'id')->where(fn ($query) => $query->where('is_available', true)->whereNotNull('user_id'))],
            'membership_plan_id' => ['required', Rule::exists('membership_plans', 'id')->where('is_active', true)],
            'payment_method' => ['required', Rule::in(['gcash', 'cash'])],
            'reference' => ['nullable', 'required_if:payment_method,gcash', 'string', 'max:100'],
            'receipt' => ['nullable', 'required_if:payment_method,gcash', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * Get the validation error messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
            'phone.string' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
            'phone.regex' => 'Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567).',
            'phone_country_code.in' => 'Only Philippine mobile numbers (+63) are accepted.',
        ];
    }
}
