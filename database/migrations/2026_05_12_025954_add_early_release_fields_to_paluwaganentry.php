<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add the new columns
        Schema::table('paluwaganentry', function (Blueprint $table) {
            $table->dateTime('releasedAt')->nullable()->after('status');
            $table->dateTime('releaseRequestedAt')->nullable()->after('releasedAt');
            $table->text('releaseNote')->nullable()->after('releaseRequestedAt');
        });

        // Step 2: Modify the enum to include 'release_requested'
        // Blueprint doesn't handle enum modification cleanly, so raw SQL is safer
        DB::statement("
            ALTER TABLE paluwaganentry 
            MODIFY COLUMN status ENUM('active', 'completed', 'cancelled', 'release_requested') 
            NOT NULL DEFAULT 'active'
        ");
    }

    public function down(): void
    {
        // Revert enum first
        DB::statement("
            ALTER TABLE paluwaganentry 
            MODIFY COLUMN status ENUM('active', 'completed', 'cancelled') 
            NOT NULL DEFAULT 'active'
        ");

        // Drop the added columns
        Schema::table('paluwaganentry', function (Blueprint $table) {
            $table->dropColumn(['releasedAt', 'releaseRequestedAt', 'releaseNote']);
        });
    }
};