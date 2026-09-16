<?php

namespace App\Http\Requests;

use App\Models\Disposal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisposalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->role === User::ROLE_ADMIN;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Disposal::decisionStatuses())],
            'response_notes' => ['nullable', 'string'],
        ];
    }
}
