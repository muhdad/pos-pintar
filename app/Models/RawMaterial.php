<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'sku',
        'name',
        'unit',
        'average_cost',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'average_cost' => 'decimal:2',
            'minimum_stock' => 'decimal:4',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function outletStocks(): HasMany
    {
        return $this->hasMany(OutletStock::class);
    }
}

