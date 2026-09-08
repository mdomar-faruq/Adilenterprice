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
            $table->date('sale_date');
            $table->foreignId('delivery_id')->constrained('employees');
            $table->foreignId('sr_id')->constrained('employees');
            $table->string('route_no');
            $table->decimal('total_damage', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('extra_amount', 15, 2)->default(0);
            $table->decimal('target_amount', 15, 2)->default(0);
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
        // Disable foreign key checks before dropping
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('sales');

        // Re-enable foreign key checks after dropping
        Schema::enableForeignKeyConstraints();
    }
};
