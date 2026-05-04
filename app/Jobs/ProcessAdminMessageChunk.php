<?php

namespace App\Jobs;

use App\Models\MessageThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAdminMessageChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Safe to retry — all inserts are wrapped in a transaction, so a failure
    // rolls back the whole chunk and a retry starts clean with no duplicates.
    public int $tries   = 3;
    public int $backoff = 10;
    public int $timeout = 120;

    public function __construct(
        public readonly int    $adminId,
        public readonly array  $recipientIds,
        public readonly string $body,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {
            foreach ($this->recipientIds as $recipientId) {
                $thread = MessageThread::where(function ($q) use ($recipientId) {
                    $q->where('user_id', $this->adminId)
                      ->where('receiver_id', $recipientId);
                })->orWhere(function ($q) use ($recipientId) {
                    $q->where('user_id', $recipientId)
                      ->where('receiver_id', $this->adminId);
                })->first();

                if (!$thread) {
                    $thread = MessageThread::create([
                        'user_id'     => $this->adminId,
                        'receiver_id' => $recipientId,
                    ]);
                }

                DB::table('user_messages')->insert([
                    'thread_id'   => $thread->id,
                    'sender_id'   => $this->adminId,
                    'receiver_id' => $recipientId,
                    'message'     => $this->body,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);

                $thread->touch();
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessAdminMessageChunk permanently failed', [
            'admin_id'      => $this->adminId,
            'recipient_ids' => $this->recipientIds,
            'error'         => $e->getMessage(),
        ]);
    }
}
