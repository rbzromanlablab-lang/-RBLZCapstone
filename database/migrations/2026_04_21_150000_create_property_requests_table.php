<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requested_item_name');
            $table->unsignedInteger('requested_quantity')->default(1);
            $table->date('needed_by')->nullable();
            $table->text('purpose');
            $table->text('additional_notes')->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('response_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('needed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_requests');
    }
};
