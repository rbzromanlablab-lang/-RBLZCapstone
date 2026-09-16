<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use App\Models\Disposal;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && in_array($this->user()->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true);
    }

    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', Rule::exists('properties', 'id')],
            'assignment_id' => ['nullable', 'integer', Rule::exists('assignments', 'id')],
            'quantity_disposed' => ['required', 'integer', 'min:1'],
            'disposal_date' => ['required', 'date'],
            'disposal_reason' => ['required', 'string'],
            'disposal_method' => ['required', Rule::in(Disposal::methods())],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $property = Property::find($this->input('property_id'));
            $assignment = filled($this->input('assignment_id'))
                ? Assignment::query()->with('property')->find($this->input('assignment_id'))
                : null;

            if (! $property) {
                return;
            }

            if ($assignment) {
                if ($assignment->property_id !== $property->id) {
                    $validator->errors()->add('property_id', 'The selected property does not match the assignment.');
                }

                if ($assignment->status !== Assignment::STATUS_ACTIVE) {
                    $validator->errors()->add('assignment_id', 'Only active assignments can be moved to disposal.');
                }

                if ((int) $this->input('quantity_disposed') > (int) $assignment->quantity_assigned) {
                    $validator->errors()->add(
                        'quantity_disposed',
                        'Only '.$assignment->quantity_assigned.' item'.((int) $assignment->quantity_assigned === 1 ? ' is' : 's are').' assigned and available for disposal.'
                    );
                }
            } elseif ((int) $this->input('quantity_disposed') > (int) $property->quantity) {
                $validator->errors()->add(
                    'quantity_disposed',
                    'Only '.$property->quantity.' item'.((int) $property->quantity === 1 ? ' is' : 's are').' available for disposal.'
                );
            }

            if ($property->status === Property::STATUS_DISPOSED) {
                $validator->errors()->add('property_id', 'This property is already marked as disposed.');
            }
        });
    }
}
