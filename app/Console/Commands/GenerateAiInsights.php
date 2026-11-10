<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Ai\AiConsultantService;
use Illuminate\Console\Command;

class GenerateAiInsights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-ai-insights {--tenant= : Optional Tenant ID} {--date= : Optional Date Y-m-d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kompilasi ringkasan penjualan harian dan hasilkan rekomendasi bisnis via AI';

    /**
     * Execute the console command.
     */
    public function handle(AiConsultantService $consultantService): int
    {
        $tenantId = $this->option('tenant');
        $date = $this->option('date') ?? date('Y-m-d');

        $query = Tenant::where('is_active', true);
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant aktif yang ditemukan.');
            return self::SUCCESS;
        }

        $this->info("Memulai pembuatan insight AI untuk tanggal: {$date}...");

        foreach ($tenants as $tenant) {
            $this->line("Memproses Tenant: {$tenant->name} ({$tenant->id})");

            try {
                // Buat insight konsolidasi seluruh cabang tenant
                $report = $consultantService->generateDailyReport($tenant->id, null, $date);
                $this->info(" -> Berhasil membuat AI Report ID: {$report->id}");

                // Buat insight per cabang jika ada
                foreach ($tenant->outlets as $outlet) {
                    $consultantService->generateDailyReport($tenant->id, $outlet->id, $date);
                    $this->comment("   -> Cabang: {$outlet->name} selesai.");
                }
            } catch (\Throwable $e) {
                $this->error("Gagal memproses tenant {$tenant->name}: {$e->getMessage()}");
            }
        }

        $this->info('Selesai membuat insight bisnis AI harian.');
        return self::SUCCESS;
    }
}

