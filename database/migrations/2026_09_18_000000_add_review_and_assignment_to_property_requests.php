<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_requests', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('selected_property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->unique()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('property_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('assignment_id');
            $table->dropConstrainedForeignId('selected_property_id');
            $table->dropColumn(['reviewed_at', 'review_notes']);
        });
    }
};
