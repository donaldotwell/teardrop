<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Repositories\RolesRepository;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are guarded.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // last_login_at,  last_seen and vendor_since are timestamps
            'last_login_at' => 'datetime',
            'last_seen' => 'datetime',
            'vendor_since' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'username_pub';
    }

    /**
     * Get the roles associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the permissions associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if the user has a role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role) : bool
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * Check if the user has a permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists() ||
            $this->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })->exists();
    }

    /**
     * Assign a role to the user.
     *
     * @param \App\Models\Role $role
     * @return void
     */
    public function assignRole(\App\Models\Role $role) : void
    {
        $this->roles()->attach($role);
    }

    /**
     * Assign a role to the user by name.
     *
     * @param string $role
     * @return void
     */
    public function assignRoleByName(string $role) : void
    {
        $role = \App\Models\Role::where('name', $role)->first();
        $this->roles()->attach($role);
    }

    /**
     * Revoke a role from the user.
     *
     * @param \App\Models\Role $role
     * @return void
     */
    public function revokeRole(\App\Models\Role $role) : void
    {
        $this->roles()->detach($role);
    }

    /**
     * Assign a permission to the user.
     *
     * @param \App\Models\Permission $permission
     * @return void
     */
    public function assignPermission(\App\Models\Permission $permission) : void
    {
        $this->permissions()->attach($permission);
    }

    /**
     * Revoke a permission from the user.
     *
     * @param \App\Models\Permission $permission
     * @return void
     */
    public function revokePermission(\App\Models\Permission $permission) : void
    {
        $this->permissions()->detach($permission);
    }

    /**
     * Get the wallets associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wallets() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Get the orders associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the balance for the specified currency.
     */
    public function getBalance(): array
    {
        $wallets = $this->wallets;
        $btcWallet = $this->btcWallet;
        $xmrWallet = $this->xmrWallet;

        // Sync BTC wallet balance if exists
        if ($btcWallet) {
            $btcWallet->updateBalance();
        }

        // Sync XMR wallet balance if exists
        if ($xmrWallet) {
            $xmrWallet->updateBalance();
        }

        return [
            'btc' => [
                'balance' => $wallets->where('currency', 'btc')->first()->balance ?? 0,
                'usd_value' => convert_crypto_to_usd($wallets->where('currency', 'btc')->first()->balance ?? 0, 'btc'),
            ],
            'xmr' => [
                'balance' => $wallets->where('currency', 'xmr')->first()->balance ?? 0,
                'usd_value' => convert_crypto_to_usd($wallets->where('currency', 'xmr')->first()->balance ?? 0, 'xmr'),
            ],
        ];
    }

    /**
     * Update the balance for the specified currency.
     */
    public function updateBalance(string $currency, float $amount): void
    {
        $wallet = $this->wallets->where('currency', $currency)->first();
        $wallet->transactions()->create([
            'amount' => $amount,
            'type' => ($amount > 0) ? 'deposit' : 'withdrawal',
        ]);
        $wallet->balance += $amount;
        $wallet->save();
    }

    /**
     * Get the messages associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMessage::class, 'sender_id')->orWhere('receiver_id', $this->id);
    }

    /**
     * Get the threads associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MessageThread::class, 'user_id')->orWhere('receiver_id', $this->id);
    }

    /**
     * Fund the user's wallets with starter funds.
     * To be only used in development or testing environments.
     * @return void
     *
     */
    public function fundWallets() : void
    {
        DB::transaction(function () {
            $starterFunds = [
                'btc' => 0, // Set starter balance for BTC
                'xmr' => 0   // Set starter balance for XMR
            ];

            foreach ($starterFunds as $currency => $amount) {
                $wallet = $this->wallets()->firstOrCreate([
                    'currency' => $currency,
                ], [
                    'balance' => 0,
                ]);

                // Fund the wallet (amount is now 0, so no funding occurs)
                $wallet->balance += $amount;
                $wallet->save();

                // Create a transaction record
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'amount'    => $amount,
                    'type'      => 'deposit',
                    'txid'      => null,
                    'comment'   => 'Initial funding',
                    'confirmed_at' => now(),
                    'completed_at' => now(),
                ]);
            }
        });
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot() : void
    {
        parent::boot();

        static::created(function ($user) {
            // Fund the user's wallets with starter funds
            $user->fundWallets();

            // Create Bitcoin wallet
            try {
                \App\Repositories\BitcoinRepository::getOrCreateWalletForUser($user);
            } catch (\Exception $e) {
                \Log::error("Failed to create Bitcoin wallet for user {$user->id}", [
                    'user_id' => $user->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Create Monero wallet
            try {
                \App\Repositories\MoneroRepository::getOrCreateWalletForUser($user);
            } catch (\Exception $e) {
                \Log::error("Failed to create Monero wallet for user {$user->id}", [
                    'user_id' => $user->id,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }

    /**
     * Get the vendor's listings.
     */
    public function listings() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function venderOrders()
    {
        return $this->hasManyThrough(
            Order::class,   // Final model
            Listing::class, // Intermediate model
            'user_id',      // Foreign key on listings table
            'listing_id',   // Foreign key on orders table
            'id',           // Local key on users table
            'id'            // Local key on listings table
        );
    }

    /**
     * Get the disputes initiated by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function initiatedDisputes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dispute::class, 'initiated_by');
    }

    /**
     * Get the disputes where this user is being disputed against.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disputesAgainst(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dispute::class, 'disputed_against');
    }

    /**
     * Get all disputes this user is involved in (either as initiator or disputed against).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allDisputes()
    {
        return Dispute::where('initiated_by', $this->id)
            ->orWhere('disputed_against', $this->id);
    }

    /**
     * Get the disputes assigned to this admin.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedDisputes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dispute::class, 'assigned_admin_id');
    }

    /**
     * Get the dispute messages sent by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disputeMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeMessage::class);
    }

    /**
     * Get the dispute evidence uploaded by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function disputeEvidence(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeEvidence::class, 'uploaded_by');
    }

    /**
     * Get the dispute evidence verified by this admin.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifiedEvidence(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DisputeEvidence::class, 'verified_by');
    }

    /**
     * Get the support tickets created by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supportTickets() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get the support tickets assigned to this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedSupportTickets() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    /**
     * Get the support ticket messages sent by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supportTicketMessages() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    /**
     * Get the support ticket attachments uploaded by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supportTicketAttachments() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class, 'uploaded_by');
    }

    /**
     * Check if the user has any of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    /**
     * Get the forum posts associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class);
    }

    /**
     * Get the forum comments associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function forumComments()
    {
        return $this->hasMany(ForumComment::class);
    }

    /**
     * Get the forum reports made by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function forumReports()
    {
        return $this->hasMany(ForumReport::class);
    }

    /**
     * Get the audit logs where this user was the target.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogsAsTarget()
    {
        return $this->hasMany(AuditLog::class, 'target_user_id');
    }

    /**
     * Get the audit logs created by this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogsAsActor()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Get all audit logs where this user performed the action
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    /**
     * Get all audit logs where this user was the target
     */
    public function targetAuditLogs()
    {
        return $this->hasMany(AuditLog::class, 'target_user_id');
    }

    /**
     * Get all audit logs related to this user (either as actor or target)
     */
    public function allAuditLogs()
    {
        return AuditLog::where('user_id', $this->id)
            ->orWhere('target_user_id', $this->id)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the Bitcoin wallet associated with the user.
     */
    public function btcWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BtcWallet::class);
    }

    /**
     * Get the Monero wallet associated with the user.
     */
    public function xmrWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(XmrWallet::class);
    }

    /**
     * Get reviews received by this vendor on their listings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function receivedReviews(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Listing::class)
            ->orderBy('reviews.created_at', 'desc');
    }

    /**
     * Get reviews given by this user as a buyer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function givenReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the average rating for this vendor.
     *
     * @param string $type 'stealth', 'quality', 'delivery', or 'overall'
     * @return float
     */
    public function getAverageRating(string $type = 'overall'): float
    {
        $reviews = $this->receivedReviews();

        if ($reviews->count() === 0) {
            return 0.0;
        }

        if ($type === 'overall') {
            $avgStealth = $reviews->avg('rating_stealth') ?? 0;
            $avgQuality = $reviews->avg('rating_quality') ?? 0;
            $avgDelivery = $reviews->avg('rating_delivery') ?? 0;
            return round(($avgStealth + $avgQuality + $avgDelivery) / 3, 2);
        }

        return round($reviews->avg('rating_' . $type) ?? 0, 2);
    }

    /**
     * Get the total number of reviews for this vendor.
     *
     * @return int
     */
    public function getTotalReviews(): int
    {
        return $this->receivedReviews()->count();
    }

    /**
     * Get the rating breakdown for this vendor.
     *
     * @return array
     */
    public function getRatingBreakdown(): array
    {
        return [
            'stealth' => $this->getAverageRating('stealth'),
            'quality' => $this->getAverageRating('quality'),
            'delivery' => $this->getAverageRating('delivery'),
            'overall' => $this->getAverageRating('overall'),
        ];
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('name', $permissions)->exists() ||
            $this->roles()->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })->exists();
    }
}
