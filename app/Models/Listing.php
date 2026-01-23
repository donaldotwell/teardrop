<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The attributes that are guarded.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * Get the reviews for this listing.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * The booting method of the model.
     * Populate the uuid field before creating the model.
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Get the user that owns the listing.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
     * Get the origin country of the listing.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function originCountry() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    /*
     * Get the destination country of the listing.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function destinationCountry() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    /**
     * Get the media for the listing.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function media() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ListingMedia::class);
    }

    /**
     * Get the product associated with the listing.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the orders associated with the listing.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all disputes related to this listing through orders.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function disputes(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Dispute::class, Order::class);
    }

    /**
     * Get the count of disputes for this listing.
     *
     * @return int
     */
    public function getDisputeCountAttribute(): int
    {
        return $this->disputes()->count();
    }

    /**
     * Check if this listing has any active disputes.
     *
     * @return bool
     */
    public function hasActiveDisputes(): bool
    {
        return $this->disputes()->open()->exists();
    }

    /**
     * Get the users who viewed this listing.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function viewers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'listing_views')
            ->withPivot('viewed_at')
            ->withTimestamps();
    }

    /**
     * Record a view for this listing.
     * Only increments view count if this is a unique view from an authenticated user.
     *
     * @param int|null $userId
     * @return bool Whether a new view was recorded
     */
    public function recordView(?int $userId = null): bool
    {
        // Only track authenticated users
        if (!$userId) {
            return false;
        }

        // Owner views don't count
        if ($userId === $this->user_id) {
            return false;
        }

        // Check if already viewed
        $exists = \DB::table('listing_views')
            ->where('listing_id', $this->id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return false; // Already viewed
        }

        // Record new view
        \DB::table('listing_views')->insert([
            'listing_id' => $this->id,
            'user_id' => $userId,
            'viewed_at' => now(),
        ]);

        // Increment view count
        $this->increment('views');

        return true;
    }

    /**
     * Check if this listing can use early finalization.
     *
     * @return bool
     */
    public function canUseEarlyFinalization(): bool
    {
        if ($this->payment_method !== 'direct') {
            return false;
        }

        $category = $this->product->productCategory;
        return $category->canVendorUseEarlyFinalization($this->user);
    }

    /**
     * Get the finalization window for this listing.
     *
     * @return \App\Models\FinalizationWindow|null
     */
    public function getFinalizationWindow(): ?\App\Models\FinalizationWindow
    {
        return $this->product->productCategory->finalizationWindow;
    }

    /**
     * Calculate total quantity sold (including pending/shipped orders to prevent overselling).
     * Only excludes cancelled orders since funds are already committed for pending/shipped.
     *
     * @return int
     */
    public function getSoldQuantity(): int
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'shipped', 'completed'])
            ->sum('quantity');
    }

    /**
     * Calculate available stock (quantity - sold).
     * Returns null if unlimited quantity.
     *
     * @return int|null
     */
    public function getAvailableStock(): ?int
    {
        // Unlimited quantity
        if ($this->quantity === null) {
            return null;
        }

        $sold = $this->getSoldQuantity();
        $available = $this->quantity - $sold;

        return max(0, $available); // Never return negative
    }

    /**
     * Check if listing is in stock.
     *
     * @return bool
     */
    public function isInStock(): bool
    {
        // Unlimited stock always available
        if ($this->quantity === null) {
            return true;
        }

        return $this->getAvailableStock() > 0;
    }

    /**
     * Check if a specific quantity is available.
     *
     * @param int $requestedQuantity
     * @return bool
     */
    public function hasAvailableStock(int $requestedQuantity): bool
    {
        // Unlimited stock always available
        if ($this->quantity === null) {
            return true;
        }

        $available = $this->getAvailableStock();
        return $available >= $requestedQuantity;
    }
}
