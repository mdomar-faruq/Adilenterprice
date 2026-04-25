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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            // Relationship to Product
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Movement Details
            $table->integer('quantity'); // Positive for Purchase (+10), Negative for Sale (-5)
            $table->integer('balance_before')->default(0); // Stock level before this transaction
            $table->integer('balance_after')->default(0);  // Stock level after this transaction

            // Metadata
            $table->enum('type', ['purchase', 'sale', 'return', 'adjustment', 'initial', 'damage_adjustment']);
            $table->string('reference_no'); // Purchase No (PUR-001) or Sale No (INV-552)
            $table->foreignId('user_id')->constrained(); // Who performed the action?

            $table->text('remarks')->nullable(); // Why was an adjustment made?
            $table->timestamps(); // Created_at is your movement date

            // Optimization for Reporting
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
