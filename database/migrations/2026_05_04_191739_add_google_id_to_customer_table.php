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
        Schema::table('customer', function (Blueprint $table) {
            // I-add ang google_id human sa email column
            $table->string('google_id')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
