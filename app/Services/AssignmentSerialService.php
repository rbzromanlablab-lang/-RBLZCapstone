<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\PropertyUnit;
use Illuminate\Validation\ValidationException;

class AssignmentSerialService
{
    // Run within the inventory transaction, after units have been assigned.
    public function apply(Assignment $assignment, ?string $input): void
    {
        $serials = array_values(array_filter(array_map('trim', preg_split('/\R/', $input ?? '')), fn ($value) => $value !== ''));
        if ($serials === []) {
            return; // Existing/generated unit serials are retained.
        }
        $units = $assignment->propertyUnits()->orderBy('id')->lockForUpdate()->get();
        if (count($serials) > $units->count() || count(array_unique($serials)) !== count($serials)) {
            throw ValidationException::withMessages(['serial_numbers' => 'Enter at most one unique serial number per assigned item.']);
        }
        foreach ($serials as $index => $serial) {
            if (mb_strlen($serial) > 255 || PropertyUnit::where('serial_number', $serial)->where('id', '!=', $units[$index]->id)->exists()) {
                throw ValidationException::withMessages(['serial_numbers' => 'Each serial must be unique and no longer than 255 characters.']);
            }
            $units[$index]->update(['serial_number' => $serial]);
        }
    }
}
