<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketKey extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'pgp_pub_key',
        'pgp_fingerprint',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns this market key.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
