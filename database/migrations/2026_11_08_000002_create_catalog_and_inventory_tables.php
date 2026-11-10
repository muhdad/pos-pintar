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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 100);
            $table->string('icon', 50)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('category_id')->nullable()->index();
            $table->string('sku', 50)->nullable();
            $table->string('barcode', 100)->nullable()->index();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('cogs', 15, 2)->default(0); // Moving average HPP
            $table->string('image_url', 255)->nullable();
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->index(['tenant_id', 'barcode']);
        });

        Schema::create('raw_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('category_id')->nullable()->index();
            $table->string('sku', 50)->nullable();
            $table->string('name', 150);
            $table->string('unit', 20)->default('gram'); // gram, ml, pcs, kg
            $table->decimal('average_cost', 15, 2)->default(0); // Unit cost / HPP
            $table->decimal('minimum_stock', 12, 4)->default(10);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id')->index();
            $table->uuid('raw_material_id')->index();
            $table->decimal('quantity', 12, 4); // Pemakaian bahan (misal 18.0000 gram)
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
            $table->unique(['product_id', 'raw_material_id']);
        });

        Schema::create('outlet_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('outlet_id')->index();
            $table->uuid('raw_material_id')->index();
            $table->decimal('current_stock', 12, 4)->default(0);
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
            $table->unique(['outlet_id', 'raw_material_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('outlet_stock_id')->index();
            $table->string('type', 30); // SALE, RESTOCK_OCR, RESTOCK_MANUAL, TRANSFER_IN, TRANSFER_OUT, WASTE, OPNAME_ADJUSTMENT
            $table->decimal('quantity_delta', 12, 4); // +/- perubahan stok
            $table->decimal('balance_after', 12, 4);
            $table->string('reference_id', 100)->nullable()->index(); // e.g. order_id, invoice_no
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('outlet_stock_id')->references('id')->on('outlet_stocks')->cascadeOnDelete();
            $table->index(['outlet_stock_id', 'created_at']);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->string('contact_name', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('outlet_stocks');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('raw_materials');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};

