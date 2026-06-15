<?php

namespace App\Services\FootballData;

use RuntimeException;

class FootballDataException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly array $payload = [],
        public readonly ?string $url = null,
    ) {
        parent::__construct($message, $status ?? 0);
    }
}
