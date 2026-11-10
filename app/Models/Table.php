<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'outlet_id',
        'name',
        'qr_code_url',
        'is_occupied',
        'current_order_id',
    ];

    protected function casts(): array
    {
        return [
            'is_occupied' => 'boolean',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}

