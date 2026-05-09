<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'icon', 'price', 'rating', 'color_class', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];
}
