<?php

namespace App\Http\Requests;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
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
            'teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'quantity_assigned' => ['required', 'integer', 'min:1'],
            'date_assigned' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $property = Property::find($this->input('property_id'));

            if (! $property) {
                return;
            }

            $availableQuantity = (int) $property->quantity;

            if ((int) $this->input('quantity_assigned') > $availableQuantity) {
                $validator->errors()->add(
                    'quantity_assigned',
                    'Only '.$availableQuantity.' item'.($availableQuantity === 1 ? ' is' : 's are').' available.'
                );
            }

            if ($property->status === Property::STATUS_DISPOSED) {
                $validator->errors()->add('property_id', 'Disposed properties cannot be assigned.');
            }

            $teacherId = $this->input('teacher_id');
            $staffId = $this->input('staff_id');

            if (blank($teacherId) && blank($staffId)) {
                $validator->errors()->add('teacher_id', 'Select either a teacher or a staff member.');
            }

            if (filled($teacherId) && filled($staffId)) {
                $validator->errors()->add('teacher_id', 'Choose only one assignee type per assignment.');
                $validator->errors()->add('staff_id', 'Choose only one assignee type per assignment.');
            }

            $teacher = User::find($teacherId);

            if ($teacher && $teacher->role !== User::ROLE_TEACHER) {
                $validator->errors()->add('teacher_id', 'Selected user must be a teacher.');
            }

            $staff = User::find($staffId);

            if ($staff && $staff->role !== User::ROLE_STAFF) {
                $validator->errors()->add('staff_id', 'Selected user must be a staff member.');
            }
        });
    }

    public function assigneeId(): int
    {
        return (int) ($this->input('teacher_id') ?: $this->input('staff_id'));
    }
}
