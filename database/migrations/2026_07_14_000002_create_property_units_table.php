<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->constrained('properties')
                ->cascadeOnDelete();
            $table->foreignId('assignment_id')
                ->nullable()
                ->constrained('assignments')
                ->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'assigned', 'disposed'])
                ->default('available');
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['assignment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_units');
    }
};
