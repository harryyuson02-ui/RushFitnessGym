<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $location = collect([
            $this->input('barangay'),
            $this->input('municipality'),
            $this->input('province'),
        ])->filter(fn (mixed $part): bool => is_string($part) && filled(trim($part)));

        if ($location->count() === 3) {
            $this->merge(['address' => $location->map(fn (string $part): string => trim($part))->implode(', ')]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isMember()
            && ($user->member === null || $user->member->canApplyForMembership());
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
            'province' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:350'],
            'availability_days' => ['required', 'array', 'min:1'],
            'availability_days.*' => ['required', 'distinct', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'availability_starts_at' => ['required', 'date_format:H:i'],
            'availability_ends_at' => ['required', 'date_format:H:i', 'after:availability_starts_at'],
            'coach_id' => ['nullable', Rule::exists('coaches', 'id')->where(fn ($query) => $query->where('is_available', true)->whereNotNull('user_id'))],
            'membership_plan_id' => ['required', Rule::exists('membership_plans', 'id')->where('is_active', true)],
            'payment_method' => ['required', Rule::in(['gcash', 'cash'])],
            'reference' => ['bail', 'nullable', 'required_if:payment_method,gcash', 'string', 'regex:/\A[0-9]{13}\z/'],
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
            'reference.required_if' => 'Enter the 13-digit GCash reference number.',
            'reference.regex' => 'The GCash reference number must contain exactly 13 digits.',
        ];
    }
}
