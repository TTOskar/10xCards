<?php

declare(strict_types=1);

namespace App\DTO\Response\AI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Response DTO containing generated flashcards and job details'
)]
final class GenerateFlashcardsResponse
{
    /**
     * @param FlashcardDTO[] $flashcards
     */
    public function __construct(
        #[OA\Property(description: 'Job ID')]
        private readonly int $id,
        
        #[OA\Property(description: 'Length of the input text in characters')]
        private readonly int $inputTextLength,
        
        #[OA\Property(description: 'Number of tokens processed by AI')]
        private readonly int $tokenCount,
        
        #[OA\Property(description: 'Number of flashcards generated')]
        private readonly int $flashcardsCount,
        
        #[OA\Property(description: 'Processing duration in milliseconds')]
        private readonly int $durationMs,
        
        #[OA\Property(description: 'Job creation timestamp', format: 'date-time')]
        private readonly \DateTimeImmutable $createdAt,
        
        #[OA\Property(
            description: 'Generated flashcards',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/FlashcardDTO')
        )]
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