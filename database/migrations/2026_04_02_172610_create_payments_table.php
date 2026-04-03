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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sales_return_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type')->default('payment'); // 'payment' or 'return',
            $table->foreignId('customer_id')->constrained();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method'); // Cash, Bank, Check
            $table->string('note')->nullable();
            $table->foreignId('user_id')->constrained(); // Who collected the money
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
