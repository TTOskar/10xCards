<?php

declare(strict_types=1);

namespace App\DTO\Response\AI;

final class GenerateFlashcardsResponse
{
    /**
     * @param FlashcardDTO[] $flashcards
     */
    public function __construct(
        private readonly int $id,
        private readonly int $inputTextLength,
        private readonly int $tokenCount,
        private readonly int $flashcardsCount,
        private readonly int $durationMs,
        private readonly \DateTimeImmutable $createdAt,
        private readonly array $flashcards
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInputTextLength(): int
    {
        return $this->inputTextLength;
    }

    public function getTokenCount(): int
    {
        return $this->tokenCount;
    }

    public function getFlashcardsCount(): int
    {
        return $this->flashcardsCount;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return FlashcardDTO[]
     */
    public function getFlashcards(): array
    {
        return $this->flashcards;
    }
} 