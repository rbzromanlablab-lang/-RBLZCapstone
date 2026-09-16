<?php

namespace App\Http\Requests;

use App\Models\PropertyRequestRecord;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequestSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_TEACHER;
    }

    public function rules(): array
    {
        return [
            'requested_item_name' => ['required', 'string', 'max:255'],
            'requested_quantity' => ['required', 'integer', 'min:1'],
            'needed_by' => ['nullable', 'date'],
            'purpose' => ['required', 'string'],
            'additional_notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(PropertyRequestRecord::statuses())],
        ];
    }
}
