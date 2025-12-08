<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $guarded = ['id'];

    /**
     * Get the wallet that owns the transaction.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wallet() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
