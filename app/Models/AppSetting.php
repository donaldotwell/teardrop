<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'category',
        'description',
        'data_type', // 'string', 'integer', 'decimal', 'boolean', 'json'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get a setting by key, with optional default value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return self::cast($setting->value, $setting->data_type);
    }

    /**
     * Set a setting by key
     */
    public static function set(string $key, mixed $value, string $category = 'general', string $dataType = 'string', string $description = ''): AppSetting
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::serialize($value, $dataType),
                'category' => $category,
                'data_type' => $dataType,
                'description' => $description,
            ]
        );
    }

    /**
     * Cast value to proper type
     */
    private static function cast(string $value, string $dataType): mixed
    {
        return match ($dataType) {
            'integer' => (int) $value,
            'decimal', 'float' => (float) $value,
            'boolean' => $value === '1' || $value === 'true',
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Serialize value to string for storage
     */
    private static function serialize(mixed $value, string $dataType): string
    {
        return match ($dataType) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Get all settings grouped by category
     */
    public static function allByCategory(): array
    {
        return self::get()->groupBy('category')->map(function ($group) {
            return $group->keyBy('key')->map->value;
        })->toArray();
    }
}
