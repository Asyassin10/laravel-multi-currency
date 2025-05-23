<?php

namespace YassineAs\MultiCurrency\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'expires_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'expires_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForPair($query, $base, $target)
    {
        return $query->where('base_currency', $base)
                    ->where('target_currency', $target);
    }
}
