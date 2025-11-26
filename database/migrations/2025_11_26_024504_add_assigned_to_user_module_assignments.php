<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_module_assignments', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('assigned_id')->nullable(); // untuk manager
            $table->string('assigned_type')->nullable(); // untuk manager
            $table->string('purpose')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_module_assignments', function (Blueprint $table) {
            //
        });
    }
};
