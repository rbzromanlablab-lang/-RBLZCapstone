<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->restrictOnDelete();
        });
    }
};
