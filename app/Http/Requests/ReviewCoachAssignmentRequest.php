<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewCoachAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCoach() ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['accepted', 'declined'])],
        ];
    }
}
