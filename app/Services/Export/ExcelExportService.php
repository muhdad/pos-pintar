<?php

namespace App\Services\Export;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    /**
     * Download spreadsheet format Excel (.xls) berstandar universal.
     * Dapat dibuka langsung oleh Microsoft Excel, Google Sheets, WPS Office, dan LibreOffice Calc
     * dengan warna, garis tabel, format angka ribuan, dan ringkasan metrik.
     *
     * @param string $filename Nama file output (.xls)
     * @param string $title Judul Utama Laporan
     * @param string $subtitle Subjudul / Periode Laporan
     * @param array $summary Ringkasan metrik [Label => Nilai]
     * @param array $headers Nama kolom tabel
     * @param array $rows Baris data (tiap sel bisa berupa nilai skalar atau array ['value' => ..., 'type' => 'number|currency|text|badge_in|badge_out'])
     * @param array|null $totals Baris total di bagian bawah (opsional)
     */
    public function download(
        string $filename,
        string $title,
        string $subtitle,
        array $summary,
        array $headers,
        array $rows,
        ?array $totals = null
    ): StreamedResponse {
        $html = $this->generateHtmlSpreadsheet($title, $subtitle, $summary, $headers, $rows, $totals);

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);
    }

    /**
     * Generate HTML Spreadsheet berstandar XML Excel dengan gridlines & Bauhaus styling.
     */
    public function generateHtmlSpreadsheet(
        string $title,
        string $subtitle,
        array $summary,
        array $headers,
        array $rows,
        ?array $totals = null
    ): string {
        $colCount = max(count($headers), 4);

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' . "\n";
        $html .= "<head>\n";
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        $html .= "<!--[if gte mso 9]>\n";
        $html .= "<xml>\n";
        $html .= " <x:ExcelWorkbook>\n";
        $html .= "  <x:ExcelWorksheets>\n";
        $html .= "   <x:ExcelWorksheet>\n";
        $html .= "    <x:Name>Laporan</x:Name>\n";
        $html .= "    <x:WorksheetOptions>\n";
        $html .= "      <x:DisplayGridlines/>\n";
        $html .= "      <x:Selected/>\n";
        $html .= "    </x:WorksheetOptions>\n";
        $html .= "   </x:ExcelWorksheet>\n";
        $html .= "  </x:ExcelWorksheets>\n";
        $html .= " </x:ExcelWorkbook>\n";
        $html .= "</xml>\n";
        $html .= "<![endif]-->\n";
        $html .= "<style>\n";
        $html .= "  body { font-family: 'Segoe UI', Calibri, Arial, sans-serif; font-size: 10pt; color: #121212; }\n";
        $html .= "  table { border-collapse: collapse; margin-bottom: 20px; }\n";
        $html .= "  .title { font-size: 15pt; font-weight: bold; color: #121212; padding: 4px 0; }\n";
        $html .= "  .subtitle { font-size: 10pt; color: #4B5563; font-style: italic; padding-bottom: 8px; }\n";
        $html .= "  .summary-header { background-color: #F3F4F6; font-weight: bold; border: 1px solid #9CA3AF; padding: 6px 10px; font-size: 9pt; text-align: left; }\n";
        $html .= "  .summary-val { background-color: #FFFFFF; font-weight: bold; border: 1px solid #9CA3AF; padding: 6px 10px; font-size: 11pt; color: #1D4ED8; text-align: left; }\n";
        $html .= "  th { background-color: #121212; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; padding: 8px 10px; font-size: 10pt; text-align: center; }\n";
        $html .= "  td { border: 1px solid #D1D5DB; padding: 6px 8px; font-size: 9.5pt; vertical-align: middle; }\n";
        $html .= "  .text { mso-number-format: '\@'; text-align: left; }\n";
        $html .= "  .center { text-align: center; mso-number-format: '\@'; }\n";
        $html .= "  .num { mso-number-format: '\#\,\#\#0'; text-align: right; }\n";
        $html .= "  .currency { mso-number-format: '\#\,\#\#0'; text-align: right; font-weight: bold; }\n";
        $html .= "  .percent { mso-number-format: '0\.0%'; text-align: right; }\n";
        $html .= "  .badge-in { background-color: #DCFCE7; color: #15803D; font-weight: bold; text-align: center; mso-number-format: '\@'; }\n";
        $html .= "  .badge-out { background-color: #FEE2E2; color: #B91C1C; font-weight: bold; text-align: center; mso-number-format: '\@'; }\n";
        $html .= "  .badge-warn { background-color: #FEF3C7; color: #B45309; font-weight: bold; text-align: center; mso-number-format: '\@'; }\n";
        $html .= "  .total-row { background-color: #FACC15; font-weight: bold; color: #121212; border-top: 2px solid #000000; border-bottom: 2px solid #000000; }\n";
        $html .= "</style>\n";
        $html .= "</head>\n";
        $html .= "<body>\n";

        // Judul & Subjudul
        $html .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
        $html .= "  <tr><td colspan=\"{$colCount}\" class=\"title\">" . htmlspecialchars($title) . "</td></tr>\n";
        $html .= "  <tr><td colspan=\"{$colCount}\" class=\"subtitle\">" . htmlspecialchars($subtitle) . "</td></tr>\n";
        $html .= "  <tr><td colspan=\"{$colCount}\"></td></tr>\n";

        // Ringkasan Metrik (Summary Cards)
        if (! empty($summary)) {
            $html .= "  <tr>\n";
            foreach ($summary as $label => $val) {
                $html .= "    <td class=\"summary-header\">" . htmlspecialchars($label) . "</td>\n";
            }
            $html .= "  </tr>\n";
            $html .= "  <tr>\n";
            foreach ($summary as $label => $val) {
                $html .= "    <td class=\"summary-val\">" . htmlspecialchars((string) $val) . "</td>\n";
            }
            $html .= "  </tr>\n";
            $html .= "  <tr><td colspan=\"{$colCount}\"></td></tr>\n";
        }
        $html .= "</table>\n";

        // Tabel Data
        $html .= "<table border=\"1\" cellpadding=\"4\" cellspacing=\"0\">\n";
        $html .= "  <thead>\n    <tr>\n";
        foreach ($headers as $header) {
            $html .= "      <th>" . htmlspecialchars($header) . "</th>\n";
        }
        $html .= "    </tr>\n  </thead>\n";
        $html .= "  <tbody>\n";

        foreach ($rows as $rowIndex => $row) {
            $bgColor = ($rowIndex % 2 === 1) ? ' style="background-color: #F9FAFB;"' : '';
            $html .= "    <tr{$bgColor}>\n";
            foreach ($row as $cell) {
                if (is_array($cell)) {
                    $val = $cell['value'] ?? '';
                    $type = $cell['type'] ?? 'text';
                    $customClass = $cell['class'] ?? $type;
                    $html .= "      <td class=\"{$customClass}\">" . htmlspecialchars((string) $val) . "</td>\n";
                } elseif (is_numeric($cell) && ! is_string($cell)) {
                    $html .= "      <td class=\"num\">{$cell}</td>\n";
                } else {
                    $html .= "      <td class=\"text\">" . htmlspecialchars((string) $cell) . "</td>\n";
                }
            }
            $html .= "    </tr>\n";
        }

        // Baris Total (Opsional)
        if (! empty($totals)) {
            $html .= "    <tr class=\"total-row\">\n";
            foreach ($totals as $totCell) {
                if (is_array($totCell)) {
                    $val = $totCell['value'] ?? '';
                    $type = $totCell['type'] ?? 'num';
                    $html .= "      <td class=\"{$type}\">" . htmlspecialchars((string) $val) . "</td>\n";
                } else {
                    $html .= "      <td class=\"text\">" . htmlspecialchars((string) $totCell) . "</td>\n";
                }
            }
            $html .= "    </tr>\n";
        }

        $html .= "  </tbody>\n";
        $html .= "</table>\n";
        $html .= "</body>\n</html>";

        return $html;
    }
}
