<?php

namespace App\Services\FootballData;

class SyncResult
{
    public function __construct(
        public string $type,
        public string $status = 'success',
        public int $itemsReceived = 0,
        public int $itemsCreated = 0,
        public int $itemsUpdated = 0,
        public ?string $message = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'status' => $this->status,
            'items_received' => $this->itemsReceived,
            'items_created' => $this->itemsCreated,
            'items_updated' => $this->itemsUpdated,
            'message' => $this->message,
        ];
    }
}
