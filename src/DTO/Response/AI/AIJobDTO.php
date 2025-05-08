<?php

namespace App\DTO\Response\AI;

use App\Enum\JobStatus;

class AIJobDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $inputTextLength,
        public readonly int $tokenCount,
        public readonly int $flashcardsCount,
        public readonly int $durationMs,
        public readonly JobStatus $status,
        public readonly \DateTimeImmutable $createdAt
    ) {
    }
} 