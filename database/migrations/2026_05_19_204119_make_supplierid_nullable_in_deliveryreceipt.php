<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveryreceipt', function (Blueprint $table) {
            $table->unsignedBigInteger('supplierID')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('deliveryreceipt', function (Blueprint $table) {
            $table->unsignedBigInteger('supplierID')->nullable(false)->change();
        });
    }
};