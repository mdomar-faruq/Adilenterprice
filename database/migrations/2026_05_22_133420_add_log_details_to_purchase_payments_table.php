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
        Schema::table('purchase_payments', function (Blueprint $table) {
            // Adding a longText column to securely store JSON maps of waterfall payment history
            $table->longText('log_details')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            // Drop column if migration is rolled back
            $table->dropColumn('log_details');
        });
    }
};
