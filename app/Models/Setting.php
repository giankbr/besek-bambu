<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected const CACHE_KEY = 'app.settings.all';

    /** @var list<string> */
    protected const ENCRYPTED_KEYS = [
        'shipping_rajaongkir_api_key',
    ];

    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::allCached()[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        if (in_array($key, self::ENCRYPTED_KEYS, true) && $value !== '') {
            try {
                return Crypt::decryptString((string) $value);
            } catch (DecryptException) {
                return $value;
            }
        }

        $decoded = json_decode((string) $value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    public static function put(string $key, mixed $value): void
    {
        if (in_array($key, self::ENCRYPTED_KEYS, true) && is_string($value) && $value !== '') {
            $stored = Crypt::encryptString($value);
        } elseif (is_scalar($value) || $value === null) {
            $stored = (string) $value;
        } else {
            $stored = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        self::query()->updateOrCreate(['key' => $key], ['value' => $stored]);

        Cache::forget(self::CACHE_KEY);
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }
}
