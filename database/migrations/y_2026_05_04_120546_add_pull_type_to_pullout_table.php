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
        Schema::table('pullout', function (Blueprint $table) {
            $table->string('pullType')->default('Preparation')->after('pullDate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pullout', function (Blueprint $table) {
            $table->dropColumn('pullType');
        });
    }
};