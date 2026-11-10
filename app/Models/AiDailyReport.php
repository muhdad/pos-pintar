<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDailyReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'report_date',
        'total_revenue',
        'total_cogs',
        'gross_profit',
        'total_transactions',
        'metrics_summary',
        'ai_insights',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'string',
            'total_revenue' => 'decimal:2',
            'total_cogs' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'total_transactions' => 'integer',
            'metrics_summary' => 'array',
            'ai_insights' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
