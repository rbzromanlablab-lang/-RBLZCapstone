<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->foreignId('assignment_id')
                ->nullable()
                ->after('property_id')
                ->constrained('assignments')
                ->nullOnDelete();
            $table->foreignId('processed_by')
                ->nullable()
                ->after('admin_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('processed_at')
                ->nullable()
                ->after('processed_by');
            $table->text('response_notes')
                ->nullable()
                ->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_id');
            $table->dropConstrainedForeignId('processed_by');
            $table->dropColumn('processed_at');
            $table->dropColumn('response_notes');
        });
    }
};
