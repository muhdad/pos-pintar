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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Support client-generated UUID for offline-first
            $table->uuid('tenant_id')->index();
            $table->uuid('outlet_id')->index();
            $table->uuid('shift_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('table_id')->nullable()->index();

            $table->string('order_number', 50)->index();
            $table->string('status', 30)->default('PENDING')->index(); // DRAFT, PENDING, PAID, CANCELLED, VOIDED, REFUNDED
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);

            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 30)->nullable(); // WhatsApp Receipt destination
            $table->timestamp('synced_at')->nullable(); // Nullable = created offline
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->foreign('table_id')->references('id')->on('tables')->nullOnDelete();
            $table->index(['tenant_id', 'outlet_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->index();
            $table->uuid('product_id')->index();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2); // Snapshot selling price
            $table->decimal('unit_cost', 15, 2)->default(0);  // Snapshot HPP/COGS at transaction time (AGENTS.md Rule 3)
            $table->decimal('subtotal', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->index();
            $table->string('method', 30); // CASH, QRIS, DEBIT, CREDIT, TRANSFER
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('SETTLED'); // PENDING, SETTLED, FAILED, EXPIRED
            $table->string('reference_no', 100)->nullable();
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50)->index(); // VOID_ORDER, MANUAL_DISCOUNT, OPEN_DRAWER, CASH_DROP
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

