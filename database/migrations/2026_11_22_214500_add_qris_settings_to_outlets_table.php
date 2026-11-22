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
        Schema::table('outlets', function (Blueprint $table) {
            $table->text('qris_payload')->nullable()->after('phone');
            $table->string('qris_merchant_name', 100)->nullable()->after('qris_payload');
            $table->string('qris_city', 50)->nullable()->after('qris_merchant_name');
            $table->string('qris_image_path', 255)->nullable()->after('qris_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn(['qris_payload', 'qris_merchant_name', 'qris_city', 'qris_image_path']);
        });
    }
};
