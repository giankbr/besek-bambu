<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image_url', 'color_class', 'drop', 'sort_order',
    ];

    protected $casts = [
        'drop' => 'boolean',
    ];
}
