<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('unit', 50)->default('piece')->after('quantity');
            $table->date('date_acquired')->nullable()->after('status');
            $table->string('condition_status', 50)->default('good')->after('date_acquired');
            $table->string('location')->nullable()->after('condition_status');
            $table->string('qr_token')->nullable()->unique()->after('location');
            $table->string('qr_code_path')->nullable()->after('qr_token');
        });

        DB::table('properties')
            ->whereNull('date_acquired')
            ->update(['date_acquired' => DB::raw('acquired_at')]);

        DB::table('properties')
            ->whereNull('qr_token')
            ->update(['qr_token' => DB::raw('qr_reference')]);
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropUnique(['qr_token']);
            $table->dropColumn([
                'unit',
                'date_acquired',
                'condition_status',
                'location',
                'qr_token',
                'qr_code_path',
            ]);
        });
    }
};
