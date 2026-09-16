<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->unsignedInteger('quantity_disposed')->default(1)->after('disposed_by');
            $table->renameColumn('disposal_type', 'disposal_method');
            $table->renameColumn('reason', 'disposal_reason');
        });
    }

    public function down(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->renameColumn('disposal_method', 'disposal_type');
            $table->renameColumn('disposal_reason', 'reason');
            $table->dropColumn('quantity_disposed');
        });
    }
};
