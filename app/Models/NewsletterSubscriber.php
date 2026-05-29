<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'email',
        'locale',
        'coupon_id',
        'welcome_sent_at',
    ];

    protected $casts = [
        'welcome_sent_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
