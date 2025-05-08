<?php

namespace App\DTO\Response\AI;

class AIGenerationResultDTO
{
    /**
     * @param GeneratedFlashcardDTO[] $flashcards
     * @param array<string, mixed> $stats
     */
    public function __construct(
        public readonly AIJobDTO $job,
        public readonly array $flashcards,
        public readonly array $stats
    ) {
    }
} 