<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('properties', 'serial_number')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->unique()->after('model');
        });
    }

    public function down(): void
    {
        // Intentionally left blank because serial_number exists in the base
        // properties migration for fresh installs.
    }
};
