<?php

namespace App\DTO\Response\AI;

use App\Enum\FlashcardStatus;

class GeneratedFlashcardDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $aiJobId,
        public readonly string $front,
        public readonly string $back,
        public readonly FlashcardStatus $status,
        public readonly ?string $editedFront,
        public readonly ?string $editedBack,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
} 