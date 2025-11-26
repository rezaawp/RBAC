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
            $table->string('model_type')->nullable()->change(); // untuk manager
            $table->unsignedBigInteger('model_id')->nullable()->change(); // untuk manager
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
