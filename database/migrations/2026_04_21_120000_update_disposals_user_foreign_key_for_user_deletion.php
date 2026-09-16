<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->dropForeign(['disposed_by']);
        });

        Schema::table('disposals', function (Blueprint $table) {
            $table->unsignedBigInteger('disposed_by')->nullable()->change();
            $table->foreign('disposed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->dropForeign(['disposed_by']);
        });

        Schema::table('disposals', function (Blueprint $table) {
            $table->unsignedBigInteger('disposed_by')->nullable(false)->change();
            $table->foreign('disposed_by')->references('id')->on('users')->restrictOnDelete();
        });
    }
};
