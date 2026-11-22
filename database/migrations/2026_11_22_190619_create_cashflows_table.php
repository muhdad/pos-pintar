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
        Schema::create('cashflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('outlet_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('type', 20)->index(); // INFLOW, OUTFLOW
            $table->string('category', 50)->index(); // SALES_CASH, SALES_NON_CASH, RESTOCK_PURCHASE, PETTY_CASH, OPERATIONAL, SALARY, RENT_UTILITIES, CAPITAL_IN, OWNER_WITHDRAWAL
            $table->decimal('amount', 15, 2);
            $table->string('source_account', 30)->default('CASH_DRAWER'); // CASH_DRAWER, BANK_ACCOUNT, PETTY_CASH
            $table->date('transaction_date')->index();
            $table->string('reference_id', 100)->nullable()->index();
            $table->string('description', 255);
            $table->string('receipt_image_path', 255)->nullable();
            $table->boolean('is_h_minus_one_deduction')->default(false); // Penanda pembelanjaan dipotong dari omset H-1
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
            $table->index(['tenant_id', 'outlet_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
