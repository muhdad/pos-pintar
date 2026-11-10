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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->string('owner_phone', 30)->index();
            $table->string('plan_tier', 50)->default('PRO_BUNDLE');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id')->index();
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('pin_hash', 255)->nullable()->after('password');
            $table->string('role', 30)->default('CASHIER')->after('pin_hash')->index();
            $table->boolean('is_active')->default(true)->after('role');

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name', 150);
            $table->string('type', 30)->default('STORE'); // STORE, CENTRAL_KITCHEN, WAREHOUSE
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('user_outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('outlet_id')->index();
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
            $table->unique(['user_id', 'outlet_id']);
        });

        Schema::create('terminals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('outlet_id')->index();
            $table->string('name', 100);
            $table->string('device_uid', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('outlet_id')->index();
            $table->string('name', 50);
            $table->string('qr_code_url')->nullable();
            $table->boolean('is_occupied')->default(false);
            $table->uuid('current_order_id')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('outlet_id')->index();
            $table->uuid('terminal_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->decimal('start_cash', 15, 2)->default(0);
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('actual_cash', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('terminal_id')->references('id')->on('terminals')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('terminals');
        Schema::dropIfExists('user_outlets');
        Schema::dropIfExists('outlets');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'phone', 'pin_hash', 'role', 'is_active']);
        });

        Schema::dropIfExists('tenants');
    }
};

