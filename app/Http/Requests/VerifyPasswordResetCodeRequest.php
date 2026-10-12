<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPasswordResetCodeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $digits = $this->input('code_digits');

        if (is_array($digits)) {
            $this->merge(['code' => implode('', $digits)]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() === null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6'],
            'code_digits' => ['nullable', 'array', 'size:6'],
            'code_digits.*' => ['required_with:code_digits', 'digits:1'],
        ];
    }
}
