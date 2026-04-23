<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Fsaid extends Model
{
    protected $table = 'fsaid';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid ??= (string) Str::uuid());
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected $fillable = [
        'uuid', 'base_id', 'vendor_id',
        'first_name', 'last_name', 'dob', 'ssn', 'gender',
        'address', 'city', 'state', 'zip', 'country', 'cs', 'description',
        'email', 'email_pass', 'fa_uname', 'fa_pass',
        'backup_code', 'security_qa', 'two_fa',
        'level', 'programs', 'enrollment', 'enrollment_details',
        'status', 'platform_buyer_id', 'platform_purchase_id', 'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(FsaidBase::class, 'base_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function platformBuyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'platform_buyer_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(FsaidPurchase::class, 'platform_purchase_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
