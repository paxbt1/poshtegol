<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_private'];

    protected function casts(): array
    {
        return ['is_private' => 'boolean'];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('app_settings')) {
            return $default;
        }

        $value = static::query()->where('key', $key)->value('value');

        return $value ?? $default;
    }

    public static function setValue(string $key, mixed $value, bool $private = false): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? (string) (int) $value : (string) $value, 'is_private' => $private],
        );
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(static::getValue($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) static::getValue($key, $default);
    }
}
