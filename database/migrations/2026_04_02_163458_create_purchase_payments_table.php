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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type')->default('payment'); // 'payment' or 'return',
            $table->foreignId('company_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method'); // Cash, Bank, Check
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained(); // Who paid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
