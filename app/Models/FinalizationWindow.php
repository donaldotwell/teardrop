<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinalizationWindow extends Model
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
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the product categories using this finalization window.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productCategories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    /**
     * Get the orders using this finalization window.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope for active finalization windows.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given time is past the dispute window.
     *
     * @param \Carbon\Carbon $referenceTime
     * @return bool
     */
    public function isExpired(Carbon $referenceTime): bool
    {
        return now()->greaterThanOrEqualTo($referenceTime);
    }

    /**
     * Get human-readable duration format.
     *
     * @return string
     */
    public function getHumanReadableDuration(): string
    {
        if ($this->duration_minutes === 0) {
            return 'Instant (No dispute window)';
        }

        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' minute' . ($this->duration_minutes > 1 ? 's' : '');
        }

        if ($this->duration_minutes < 1440) {
            $hours = round($this->duration_minutes / 60, 1);
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        if ($this->duration_minutes < 10080) {
            $days = round($this->duration_minutes / 1440, 1);
            return $days . ' day' . ($days > 1 ? 's' : '');
        }

        $weeks = round($this->duration_minutes / 10080, 1);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '');
    }

    /**
     * Get the count of categories using this window.
     *
     * @return int
     */
    public function getCategoriesCountAttribute(): int
    {
        return $this->productCategories()->count();
    }

    /**
     * Get the count of orders using this window.
     *
     * @return int
     */
    public function getOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }
}
