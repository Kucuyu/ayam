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
        Schema::table('member', function (Blueprint $table) {
            Schema::table('member', function (Blueprint $table) {
            $table->renameColumn('points', 'poin'); // Ganti nama kolom dari points ke poin
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member', function (Blueprint $table) {
             Schema::table('member', function (Blueprint $table) {
            $table->renameColumn('poin', 'points'); // Kembalikan nama kolom ke points
        });
        });
    }
};
