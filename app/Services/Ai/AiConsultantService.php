<?php

namespace App\Services\Ai;

use App\Models\AiDailyReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiConsultantService
{
    public function __construct(
        protected BusinessDataAggregator $aggregator
    ) {}

    /**
     * Hasilkan insight bisnis harian dari agregat metrik menggunakan LLM (Google Gemini / OpenAI).
     *
     * @param string $tenantId
     * @param string|null $outletId
     * @param string|null $date
     * @return AiDailyReport
     */
    public function generateDailyReport(string $tenantId, ?string $outletId = null, ?string $date = null): AiDailyReport
    {
        $metrics = $this->aggregator->aggregateDailyMetrics($tenantId, $outletId, $date);

        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        $insights = null;

        if ($apiKey) {
            $insights = $this->callGeminiApi($metrics, $apiKey);
        }

        // Fallback jika API key belum dikonfigurasi atau koneksi offline
        if (! $insights) {
            $insights = $this->generateFallbackInsights($metrics);
        }

        return AiDailyReport::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'outlet_id' => $outletId,
                'report_date' => $metrics['date'],
            ],
            [
                'total_revenue' => $metrics['financial']['total_revenue'],
                'total_cogs' => $metrics['financial']['total_cogs'],
                'gross_profit' => $metrics['financial']['gross_profit'],
                'total_transactions' => $metrics['financial']['transactions'],
                'metrics_summary' => $metrics,
                'ai_insights' => $insights,
            ]
        );
    }

    /**
     * Kirim prompt terstruktur ke Google Gemini API via Laravel Http Client.
     */
    protected function callGeminiApi(array $metrics, string $apiKey): ?array
    {
        $prompt = "Kamu adalah Konsultan Bisnis AI Senior untuk usaha F&B dan Retail UMKM Indonesia.
Analisis data performa penjualan harian berikut dan berikan rekomendasi bisnis taktis dalam format JSON murni.

DATA METRIK:
" . json_encode($metrics, JSON_PRETTY_PRINT) . "

Instruksi Format Response (JSON Schema):
{
  \"executive_summary\": \"Ringkasan 2-3 kalimat kondisi bisnis hari ini\",
  \"health_score\": 85,
  \"strengths\": [\"hal positif 1\", \"hal positif 2\"],
  \"risks\": [\"risiko/kendala 1\"],
  \"actionable_recommendations\": [
    {
      \"title\": \"Judul saran\",
      \"action\": \"Langkah konkret yang harus dilakukan owner besok\",
      \"priority\": \"HIGH / MEDIUM / LOW\"
    }
  ],
  \"bundling_ideas\": [
    {
      \"bundle_name\": \"Nama Paket Promo\",
      \"items\": [\"Menu Laris\", \"Menu Lambat Laku\"],
      \"strategy\": \"Alasan memadukan menu ini untuk mengurangi dead stock\"
    }
  ],
  \"fraud_assessment\": {
    \"risk_level\": \"LOW / MEDIUM / HIGH\",
    \"notes\": \"Komentar terkait frekuensi void atau transaksi mencurigakan\"
  }
}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(15)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.3,
                ],
            ]);

            if ($response->successful()) {
                $content = $response->json('candidates.0.content.parts.0.text');
                return json_decode($content, true);
            }

            Log::error('Gemini API Error: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Gemini Request Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Logika fallback heuristik lokal jika koneksi ke LLM eksternal tidak aktif.
     */
    protected function generateFallbackInsights(array $metrics): array
    {
        $fin = $metrics['financial'];
        $voidCount = $metrics['anomalies']['void_count'] ?? 0;

        $fraudRisk = $voidCount > 3 ? 'HIGH' : ($voidCount > 0 ? 'MEDIUM' : 'LOW');

        $recommendations = [];
        if (! empty($metrics['critical_stock'])) {
            $recommendations[] = [
                'title' => 'Pengadaan Bahan Baku Kritis Segera',
                'action' => 'Segera restock ' . count($metrics['critical_stock']) . ' bahan baku yang berada di bawah ambang batas aman.',
                'priority' => 'HIGH',
            ];
        }

        if (! empty($metrics['slow_moving']) && ! empty($metrics['top_selling'])) {
            $topName = $metrics['top_selling'][0]['name'] ?? 'Menu Unggulan';
            $slowName = $metrics['slow_moving'][0] ?? 'Menu Kurang Laris';
            $recommendations[] = [
                'title' => "Paket Bundling {$topName} + {$slowName}",
                'action' => "Jual paket kombo diskon 10% untuk mendongkrak penjualan {$slowName}.",
                'priority' => 'MEDIUM',
            ];
        }

        if (empty($recommendations)) {
            $topName = ! empty($metrics['top_selling']) ? $metrics['top_selling'][0]['name'] : 'Menu Andalan';
            $recommendations[] = [
                'title' => "Optimasi Penjualan {$topName}",
                'action' => "Jadikan {$topName} sebagai rekomendasi aktif kasir di jam sibuk untuk memaksimalkan average order value (AOV).",
                'priority' => 'MEDIUM',
            ];
            $recommendations[] = [
                'title' => 'Pertahankan Konsistensi Takaran Resep (BOM)',
                'action' => "Margin laba kotor saat ini stabil di level {$fin['profit_margin_pct']}%. Pastikan tim mematuhi standar gramasi bahan baku untuk menjaga profitabilitas.",
                'priority' => 'LOW',
            ];
        }

        return [
            'executive_summary' => sprintf(
                'Total omzet tercatat Rp%s dengan laba kotor Rp%s (margin %s%%) dari %d transaksi.',
                number_format($fin['total_revenue'], 0, ',', '.'),
                number_format($fin['gross_profit'], 0, ',', '.'),
                $fin['profit_margin_pct'],
                $fin['transactions']
            ),
            'health_score' => $fin['total_revenue'] > 0 ? 80 : 50,
            'strengths' => [
                'Margin kotor berada pada level ' . $fin['profit_margin_pct'] . '%',
                'Rata-rata belanja pelanggan (AOV): Rp' . number_format($fin['aov'], 0, ',', '.'),
            ],
            'risks' => count($metrics['critical_stock']) > 0
                ? ['Terdapat ' . count($metrics['critical_stock']) . ' bahan baku dalam status kritis.']
                : [],
            'actionable_recommendations' => $recommendations,
            'bundling_ideas' => [],
            'fraud_assessment' => [
                'risk_level' => $fraudRisk,
                'notes' => $voidCount > 0
                    ? "Terdeteksi {$voidCount} transaksi dibatalkan (void). Harap verifikasi kepatuhan kasir."
                    : 'Tidak ada indikasi anomali pembatalan pesanan.',
            ],
        ];
    }
}

