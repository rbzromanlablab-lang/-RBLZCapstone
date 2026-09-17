<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Property;
use App\Models\PropertyHistory;
use App\Models\PropertyRequestRecord;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RequestAssignmentService
{
    // Called inside the request's transaction, after locking the request record.
    public function assign(PropertyRequestRecord $request, int $propertyId, User $issuer): Assignment
    {
        $property = Property::query()->lockForUpdate()->findOrFail($propertyId);
        $quantity = $request->requested_quantity;
        if ($quantity < 1 || $property->quantity < $quantity || in_array($property->status, [Property::STATUS_DISPOSED, Property::STATUS_FOR_DISPOSAL], true)) {
            throw ValidationException::withMessages(['property_id' => 'This property does not have enough assignable stock. Choose another property or mark the request as awaiting stock.']);
        }
        $recipient = $request->requester;
        if (! $recipient || ! $recipient->is_active || ! $recipient->isTeacher()) {
            throw ValidationException::withMessages(['status' => 'The requester must be an active end-user.']);
        }

        // Maintain serialized inventory in the same way as direct assignments.
        $missing = max(0, $property->quantity - $property->availableUnits()->count());
        for ($i = 0; $i < $missing; $i++) {
            $property->units()->create(['serial_number' => Property::generateSerialNumber(), 'status' => PropertyUnit::STATUS_AVAILABLE]);
        }
        $units = $property->availableUnits()->lockForUpdate()->limit($quantity)->get();
        if ($units->count() !== $quantity) {
            throw ValidationException::withMessages(['property_id' => 'Not enough available property units.']);
        }

        $assignment = Assignment::create([
            'property_id' => $property->id,
            'teacher_id' => $recipient->id,
            'teacher_profile_id' => $recipient->teacherProfile?->id,
            'assigned_by' => $issuer->id,
            'staff_id' => $issuer->staffProfile?->id,
            'quantity_assigned' => $quantity,
            'date_assigned' => today(),
            'assigned_at' => today(),
            'location' => $property->location,
            'location_id' => $property->location_id,
            'remarks' => 'Approved property request #'.$request->id.': '.$request->requested_item_name,
            'status' => Assignment::STATUS_ACTIVE,
        ]);
        PropertyUnit::query()->whereIn('id', $units->pluck('id'))->update([
            'assignment_id' => $assignment->id, 'status' => PropertyUnit::STATUS_ASSIGNED, 'updated_at' => now(),
        ]);
        $property->decrement('quantity', $quantity);
        $property->refresh()->syncInventoryStatus();
        PropertyHistory::create([
            'property_id' => $property->id, 'action_type' => 'assigned',
            'reference' => 'assignment:'.$assignment->id, 'action_date' => today(),
            'remarks' => $assignment->remarks,
        ]);

        return $assignment;
    }
}
