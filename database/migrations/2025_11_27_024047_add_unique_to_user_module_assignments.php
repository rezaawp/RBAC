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
            $table->unique(
                ['assigned_id', 'assigned_type', 'user_id', 'purpose'],
                'unique_assignment'
            );
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
