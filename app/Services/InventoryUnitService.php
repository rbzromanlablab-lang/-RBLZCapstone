<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Validation\ValidationException;

class InventoryUnitService
{
    // Call inside a transaction with the property row locked.
    public function sync(Property $property, ?array $rows = null): void
    {
        $available = $property->availableUnits()->lockForUpdate()->get();
        $quantity = (int) $property->quantity;

        if ($rows !== null && count($rows) !== $quantity) {
            throw ValidationException::withMessages(['quantity' => 'Provide one serial number field for each available unit.']);
        }

        $rows ??= collect(range(0, max(0, $quantity - 1)))->take($quantity)->map(function ($index) use ($available, $property) {
            return [
                'id' => $available->get($index)?->id,
                'serial_number' => $available->get($index)?->serial_number
                    ?? ($index === 0 && ! $property->units()->exists() ? $property->serial_number : null),
            ];
        })->all();

        $seen = [];
        $retainedIds = [];
        foreach ($rows as $index => $row) {
            $unit = ! empty($row['id']) ? $available->firstWhere('id', (int) $row['id']) : null;
            if (! empty($row['id']) && (! $unit || in_array($unit->id, $retainedIds, true))) {
                throw ValidationException::withMessages(['units.'.$index.'.serial_number' => 'This unit is no longer available. Reload the property before editing.']);
            }
            $serial = trim((string) ($row['serial_number'] ?? ''));
            $serial = $serial !== '' ? $serial : ($unit?->serial_number ?: Property::generateSerialNumber());
            $key = mb_strtolower($serial);
            $duplicateUnit = PropertyUnit::whereRaw('LOWER(serial_number) = ?', [$key])
                ->when($unit, fn ($query) => $query->where('id', '!=', $unit->id))->exists();
            $duplicateProperty = Property::where('id', '!=', $property->id)
                ->whereRaw('LOWER(serial_number) = ?', [$key])->exists();
            if (isset($seen[$key]) || $duplicateUnit || $duplicateProperty) {
                throw ValidationException::withMessages(['units.'.$index.'.serial_number' => 'Each unit must have a different serial number. This serial number is already in use.']);
            }
            $seen[$key] = true;
            if ($unit) {
                $unit->update(['serial_number' => $serial]);
            } else {
                $unit = $property->units()->create(['serial_number' => $serial, 'status' => PropertyUnit::STATUS_AVAILABLE]);
            }
            $retainedIds[] = $unit->id;
        }

        $property->availableUnits()->whereNotIn('id', $retainedIds)->delete();
        $firstSerial = $property->units()->orderBy('id')->value('serial_number');
        if ($firstSerial) $property->update(['serial_number' => $firstSerial]);
    }

    public function assign(Property $property, Assignment $assignment, int $quantity, ?array $unitIds = null): void
    {
        if ($unitIds === null) {
            // Compatibility for older inventory records that predate per-unit tracking.
            $missing = max(0, (int) $property->quantity - $property->availableUnits()->count());
            for ($i = 0; $i < $missing; $i++) {
                $serial = ! $property->units()->exists() && $property->serial_number
                    && ! PropertyUnit::where('serial_number', $property->serial_number)->exists()
                    ? $property->serial_number : Property::generateSerialNumber();
                $property->units()->create(['serial_number' => $serial, 'status' => PropertyUnit::STATUS_AVAILABLE]);
            }
        } elseif (count($unitIds) !== $quantity || count(array_unique($unitIds)) !== $quantity) {
            throw ValidationException::withMessages(['unit_ids' => 'Select exactly '.$quantity.' available unit(s).']);
        }

        $units = $property->availableUnits()->whereNull('assignment_id')
            ->when($unitIds !== null, fn ($query) => $query->whereIn('id', $unitIds))
            ->lockForUpdate()->limit($quantity)->get();
        if ($units->count() !== $quantity) {
            throw ValidationException::withMessages(['unit_ids' => 'One or more selected units are no longer available for this property. Reload and select available units.']);
        }
        PropertyUnit::whereIn('id', $units->pluck('id'))->update([
            'assignment_id' => $assignment->id,
            'status' => PropertyUnit::STATUS_ASSIGNED,
            'updated_at' => now(),
        ]);
    }
}
