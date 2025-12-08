<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
    protected $guarded = ['id'];

    /**
     * The booting method of the model.
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        // Populate the uuid field before creating a new thread
        static::creating(function ($thread) {
            $thread->uuid = \Illuminate\Support\Str::uuid();
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName() : string
    {
        return 'uuid';
    }

    /**
     * Get the user of the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the receiver of the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the order that the message belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the messages associated with the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserMessage::class, 'thread_id');
    }

    /**
     * Get the latest message associated with the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestMessage() : \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserMessage::class, 'thread_id')->latest();
    }
}
