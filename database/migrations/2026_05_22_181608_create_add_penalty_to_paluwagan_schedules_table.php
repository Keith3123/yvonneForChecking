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
        Schema::table('paluwaganschedule', function (Blueprint $table) {
        $table->decimal('penaltyAmount', 20, 2)->default(0)->after('amountPaid');
        $table->date('gracePeriodEnd')->nullable()->after('penaltyAmount');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('paluwaganschedule', function (Blueprint $table) {
        $table->dropColumn(['penaltyAmount', 'gracePeriodEnd']);
    });
}
};
