<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('paluwaganitems', function (Blueprint $table) {
        $table->id('itemID');
        $table->string('name');
        $table->string('category')->nullable(); // e.g. "Lechon", "Tray", etc.
        $table->boolean('isActive')->default(1);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paluwaganitems');
    }
};
