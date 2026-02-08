<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get rules by category, ordered by position
     */
    public static function getByCategory(string $category)
    {
        return self::where('category', $category)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get all active rules grouped by category
     */
    public static function getAllGrouped()
    {
        return self::where('is_active', true)
            ->orderBy('category')
            ->orderBy('order')
            ->get()
            ->groupBy('category');
    }
}
