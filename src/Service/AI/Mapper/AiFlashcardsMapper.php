<?php

declare(strict_types=1);

namespace App\Service\AI\Mapper;

use App\DTO\Response\AI\FlashcardDTO;
use App\DTO\Response\AI\GenerateFlashcardsResponse;
use App\Entity\AI\AiJob;
use App\Entity\AI\AiJobFlashcard;

final class AiFlashcardsMapper
{
    public function mapJobToResponse(AiJob $aiJob): GenerateFlashcardsResponse
    {
        return new GenerateFlashcardsResponse(
            id: $aiJob->getId() ?? 0,
            inputTextLength: $aiJob->getInputTextLength(),
            tokenCount: $aiJob->getTokenCount(),
            flashcardsCount: $aiJob->getFlashcardsCount(),
            durationMs: $aiJob->getDurationMs(),
            createdAt: $aiJob->getCreatedAt(),
            flashcards: array_map(
                callback: fn (AiJobFlashcard $flashcard): FlashcardDTO => $this->mapFlashcardToDTO(flashcard: $flashcard),
                array: $aiJob->getFlashcards()->toArray()
            )
        );
    }

    public function mapFlashcardToDTO(AiJobFlashcard $flashcard): FlashcardDTO
    {
        return new FlashcardDTO(
            front: $flashcard->getCurrentFront(),
            back: $flashcard->getCurrentBack()
        );
    }
} 