<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, HasUuids;

    public const UPDATED_AT = null; // Stock movements are append-only

    protected $fillable = [
        'outlet_stock_id',
        'type',
        'quantity_delta',
        'balance_after',
        'reference_id',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_delta' => 'decimal:4',
            'balance_after' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    public function outletStock(): BelongsTo
    {
        return $this->belongsTo(OutletStock::class);
    }
}

