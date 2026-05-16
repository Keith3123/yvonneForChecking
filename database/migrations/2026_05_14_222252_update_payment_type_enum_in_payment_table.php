<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `payment` 
            MODIFY COLUMN `paymentType` 
            ENUM('fullpayment', 'partial', 'downpayment', 'remaining_balance') 
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `payment` 
            MODIFY COLUMN `paymentType` 
            ENUM('fullpayment', 'partial') 
            NOT NULL
        ");
    }
};