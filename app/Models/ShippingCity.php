<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingCity extends Model
{
    protected $fillable = [
        'id',
        'province_id',
        'province_name',
        'type',
        'name',
        'postal_code',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function province(): BelongsTo
    {
        return $this->belongsTo(ShippingProvince::class, 'province_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $type = $this->type ? trim($this->type).' ' : '';

        return $type.$this->name;
    }
}
