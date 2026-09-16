<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->string('qr_token')->nullable()->unique()->after('serial_number');
        });

        DB::table('property_units')
            ->whereNull('qr_token')
            ->orderBy('id')
            ->each(function ($unit): void {
                DB::table('property_units')
                    ->where('id', $unit->id)
                    ->update(['qr_token' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
