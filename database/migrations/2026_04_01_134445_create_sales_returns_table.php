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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('sale_id')->nullable()->constrained(); // Optional: Link to original sale
            $table->string('return_no')->unique();
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2);
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
        Schema::dropIfExists('sales_returns');
    }
};
