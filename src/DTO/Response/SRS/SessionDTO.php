<?php

namespace App\DTO\Response\SRS;

class SessionDTO
{
    /**
     * @param array<string, mixed>|null $sessionStats
     */
    public function __construct(
        public readonly ?SessionCardDTO $card,
        public readonly ?array $sessionStats = null
    ) {
    }
} 