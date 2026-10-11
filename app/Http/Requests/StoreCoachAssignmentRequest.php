<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCoachAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->user()?->member;

        return $this->user()?->isMember()
            && $member !== null
            && $member->coach_id === null
            && $member->subscriptions()->active()->exists()
            && ! $member->coachAssignmentRequests()->where('status', 'pending')->exists();
    }

    public function rules(): array
    {
        return [
            'coach_id' => [
                'required',
                Rule::exists('coaches', 'id')->where(fn ($query) => $query
                    ->where('is_available', true)
                    ->whereNotNull('user_id')),
            ],
        ];
    }
}
