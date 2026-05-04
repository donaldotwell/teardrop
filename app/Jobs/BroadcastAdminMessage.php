<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BroadcastAdminMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // No retries — a partial failure would re-dispatch already-dispatched chunks.
    public int $tries   = 1;
    public int $timeout = 120;

    public function __construct(
        public readonly int    $adminId,
        public readonly string $scope,
        public readonly string $body,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $query = User::where('status', '!=', 'banned')
                     ->where('id', '!=', $this->adminId);

        match ($this->scope) {
            'vendors'    => $query->whereHas('roles', fn($q) => $q->where('name', 'vendor')),
            'moderators' => $query->whereHas('roles', fn($q) => $q->where('name', 'moderator')),
            'all'        => $query->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin')),
        };

        $dispatched = 0;

        $query->chunkById(100, function ($users) use (&$dispatched) {
            ProcessAdminMessageChunk::dispatch(
                $this->adminId,
                $users->pluck('id')->all(),
                $this->body,
            );
            $dispatched++;
        });

        Log::info('BroadcastAdminMessage: dispatched chunk jobs', [
            'admin_id'   => $this->adminId,
            'scope'      => $this->scope,
            'chunks'     => $dispatched,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('BroadcastAdminMessage orchestrator failed', [
            'admin_id' => $this->adminId,
            'scope'    => $this->scope,
            'error'    => $e->getMessage(),
        ]);
    }
}
