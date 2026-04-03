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
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); // Unsigned Big Integer
            $table->string('invoice_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->date('sale_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, partial, paid
            $table->text('remarks')->nullable();
            $table->foreignId('user_id')->constrained('users'); // Prepared by
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
