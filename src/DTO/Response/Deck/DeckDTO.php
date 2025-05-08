<?php

namespace App\DTO\Response\Deck;

class DeckDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
} 