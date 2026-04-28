<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMessage extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['read_at' => 'datetime'];

    /**
     * The booting method of the model.
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($message) {
            if ($message->thread_id) {
                return;
            }

            // Look for an existing thread in either direction between the two users
            $thread = MessageThread::where(function ($q) use ($message) {
                $q->where('user_id', $message->sender_id)
                  ->where('receiver_id', $message->receiver_id);
            })->orWhere(function ($q) use ($message) {
                $q->where('user_id', $message->receiver_id)
                  ->where('receiver_id', $message->sender_id);
            })->first();

            if (!$thread) {
                $thread = MessageThread::create([
                    'user_id'     => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                ]);
            }

            $message->thread_id = $thread->id;
        });
    }

    /**
     * Get the sender of the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
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

}
