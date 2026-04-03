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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('purchase_price', 15, 2)->default(0); // Used for COGS calculation
            $table->integer('percent')->default(0);               // Used for COGS calculation
            $table->decimal('sale_price', 15, 2)->default(0);     // Default selling price
            $table->decimal('stock', 15, 2)->default(0);     // Default selling price
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('valid')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
