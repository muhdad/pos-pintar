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
        Schema::create('ai_daily_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('outlet_id')->nullable()->index();
            $table->date('report_date')->index();
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_cogs', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->json('metrics_summary'); // Compact summary from BusinessDataAggregator
            $table->json('ai_insights')->nullable(); // Tactical recommendations & anomalies from LLM
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('outlet_id')->references('id')->on('outlets')->nullOnDelete();
            $table->unique(['tenant_id', 'outlet_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_daily_reports');
    }
};

