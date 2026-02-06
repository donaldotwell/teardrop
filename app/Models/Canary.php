<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canary extends Model
{
    protected $fillable = [
        'message',
    ];

    /**
     * Get the latest canary.
     */
    public static function latest()
    {
        return static::orderBy('created_at', 'desc')->first();
    }
}
