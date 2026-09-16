<?php

use App\Models\Assignment;
use App\Models\PropertyUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('properties')
            ->orderBy('id')
            ->get()
            ->each(function ($property): void {
                $this->createMissingAvailableUnits($property);
                $this->createMissingAssignedUnits($property);
            });
    }

    public function down(): void
    {
        // Keep generated unit rows. Deleting them here could remove QR history
        // from systems that already printed or attached unit QR codes.
    }

    private function createMissingAvailableUnits(object $property): void
    {
        $existingAvailableUnits = DB::table('property_units')
            ->where('property_id', $property->id)
            ->where('status', PropertyUnit::STATUS_AVAILABLE)
            ->count();

        $missingUnits = max(0, (int) $property->quantity - $existingAvailableUnits);

        for ($i = 0; $i < $missingUnits; $i++) {
            $this->insertUnit((int) $property->id, null, PropertyUnit::STATUS_AVAILABLE);
        }
    }

    private function createMissingAssignedUnits(object $property): void
    {
        DB::table('assignments')
            ->where('property_id', $property->id)
            ->where('status', Assignment::STATUS_ACTIVE)
            ->orderBy('id')
            ->get()
            ->each(function ($assignment) use ($property): void {
                $existingAssignedUnits = DB::table('property_units')
                    ->where('property_id', $property->id)
                    ->where('assignment_id', $assignment->id)
                    ->where('status', PropertyUnit::STATUS_ASSIGNED)
                    ->count();

                $missingUnits = max(0, (int) $assignment->quantity_assigned - $existingAssignedUnits);

                for ($i = 0; $i < $missingUnits; $i++) {
                    $this->insertUnit((int) $property->id, (int) $assignment->id, PropertyUnit::STATUS_ASSIGNED);
                }
            });
    }

    private function insertUnit(int $propertyId, ?int $assignmentId, string $status): void
    {
        DB::table('property_units')->insert([
            'property_id' => $propertyId,
            'assignment_id' => $assignmentId,
            'serial_number' => $this->uniqueSerialNumber(),
            'qr_token' => $this->uniqueQrToken(),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function uniqueSerialNumber(): string
    {
        do {
            $serialNumber = 'SN-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (
            DB::table('properties')->where('serial_number', $serialNumber)->exists()
            || DB::table('property_units')->where('serial_number', $serialNumber)->exists()
        );

        return $serialNumber;
    }

    private function uniqueQrToken(): string
    {
        do {
            $token = (string) Str::uuid();
        } while (DB::table('property_units')->where('qr_token', $token)->exists());

        return $token;
    }
};
