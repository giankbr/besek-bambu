<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'name',
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

    public function emailLogs(): HasMany
    {
        return $this->hasMany(NewsletterEmailLog::class)->latest('sent_at');
    }

    public function displayName(): string
    {
        $name = trim((string) $this->name);

        if ($name !== '') {
            return $name;
        }

        $local = strstr($this->email, '@', true);

        return $local !== false && $local !== '' ? $local : $this->email;
    }
}
