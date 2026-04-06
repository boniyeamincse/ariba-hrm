<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OpdQueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int|string|null $tenantId,
        public readonly string $action,
        public readonly array $payload = []
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('opd.queue.'.$this->tenantId)];
    }

    public function broadcastAs(): string
    {
        return 'opd.queue.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'action' => $this->action,
            'payload' => $this->payload,
            'timestamp' => now()->toISOString(),
        ];
    }
}
