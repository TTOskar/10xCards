<?php

namespace App\DTO\Response\Card;

class CardDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $deckId,
        public readonly string $front,
        public readonly string $back,
        public readonly string $source,
        public readonly \DateTimeInterface $dueDate,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
} 