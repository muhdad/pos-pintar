<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'address',
        'phone',
        'latitude',
        'longitude',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets');
    }

    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function outletStocks(): HasMany
    {
        return $this->hasMany(OutletStock::class);
    }
}

