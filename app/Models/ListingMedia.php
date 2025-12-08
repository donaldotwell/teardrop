<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingMedia extends Model
{
    protected $fillable = [
        'listing_id',
        'content',
        'type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the listing that owns the media.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the base64 data URI for direct rendering.
     */
    public function getDataUriAttribute(): string
    {
        return "data:{$this->type};base64,{$this->content}";
    }
}
