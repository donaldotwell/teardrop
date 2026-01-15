<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are guarded.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'allows_early_finalization' => 'boolean',
    ];

    /**
     * Get the products associated with the product category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the listings associated with the product category.
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function listings() : \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Listing::class, Product::class);
    }

    /**
     * Get the finalization window for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function finalizationWindow(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinalizationWindow::class);
    }

    /**
     * Check if this category allows early finalization.
     *
     * @return bool
     */
    public function allowsEarlyFinalization(): bool
    {
        return $this->allows_early_finalization &&
               $this->finalization_window_id !== null &&
               $this->finalizationWindow &&
               $this->finalizationWindow->is_active;
    }

    /**
     * Check if a vendor can use early finalization for this category.
     *
     * @param \App\Models\User $vendor
     * @return bool
     */
    public function canVendorUseEarlyFinalization(User $vendor): bool
    {
        if (!$this->allowsEarlyFinalization()) {
            return false;
        }

        return $vendor->vendor_level >= $this->min_vendor_level_for_early &&
               $vendor->early_finalization_enabled &&
               $vendor->status === 'active';
    }

    /**
     * Get the finalization window duration in minutes.
     *
     * @return int|null
     */
    public function getFinalizationWindowDuration(): ?int
    {
        if (!$this->finalizationWindow) {
            return null;
        }

        return $this->finalizationWindow->duration_minutes;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot() : void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
