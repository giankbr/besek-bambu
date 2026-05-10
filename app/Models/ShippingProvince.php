<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingProvince extends Model
{
    protected $fillable = ['id', 'name'];

    public $incrementing = false;

    protected $keyType = 'string';

    public function cities(): HasMany
    {
        return $this->hasMany(ShippingCity::class, 'province_id');
    }
}
