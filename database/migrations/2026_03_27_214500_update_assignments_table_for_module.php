<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->unsignedInteger('quantity_assigned')->default(1)->after('assigned_by');
            $table->date('date_assigned')->nullable()->after('quantity_assigned');
        });

        DB::table('assignments')
            ->whereNull('date_assigned')
            ->update(['date_assigned' => DB::raw('assigned_at')]);
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_assigned',
                'date_assigned',
            ]);
        });
    }
};
