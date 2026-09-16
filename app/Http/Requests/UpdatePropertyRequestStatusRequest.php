<?php

namespace App\Http\Requests;

use App\Models\PropertyRequestRecord;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->role === User::ROLE_STAFF;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(PropertyRequestRecord::statuses())],
            'response_notes' => ['nullable', 'string'],
        ];
    }
}
