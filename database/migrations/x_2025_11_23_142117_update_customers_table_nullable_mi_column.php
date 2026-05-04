<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class  UpdateCustomersTableNullableMiColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('mi')->nullable();  // Middle Initial, nullable
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('address');
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('isActive')->default(1);  // Active status default to 1
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
}
