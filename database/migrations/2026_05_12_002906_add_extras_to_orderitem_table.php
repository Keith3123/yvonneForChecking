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
    Schema::table('orderitem', function (Blueprint $table) {
        $table->string('size')->nullable()->after('qty');
        $table->string('message')->nullable()->after('size');
        $table->json('customization')->nullable()->after('message');
        $table->json('includes')->nullable()->after('customization');
    });
}

public function down()
{
    Schema::table('orderitem', function (Blueprint $table) {
        $table->dropColumn(['size', 'message', 'customization', 'includes']);
    });
}
};
