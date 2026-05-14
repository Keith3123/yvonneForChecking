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
    Schema::table('paluwaganentry', function (Blueprint $table) {
        $table->unsignedTinyInteger('startDay')->nullable()->after('startMonth')->default(15);
    });
}

public function down()
{
    Schema::table('paluwaganentry', function (Blueprint $table) {
        $table->dropColumn('startDay');
    });
}
};
