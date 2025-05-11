<?php

declare(strict_types=1);

namespace App\Service\AI;

use App\DTO\Request\AI\BulkSaveFlashcardsDTO;
use App\DTO\Request\AI\UpdateFlashcardDTO;
use App\DTO\Response\AI\AIJobFlashcardsResponseDTO;
use App\DTO\Response\AI\FlashcardResponseDTO;
use App\Exception\AI\JobNotFoundException;
use App\Exception\AI\FlashcardNotFoundException;
use App\Exception\AI\InvalidStatusTransitionException;
use App\Exception\Deck\DeckNotFoundException;
use App\Exception\AI\NoFlashcardsToProcessException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

interface FlashcardServiceInterface
{
    /**
     * Retrieves flashcards for a specific AI job with pagination.
     *
     * @throws JobNotFoundException if the job does not exist
     * @throws AccessDeniedException if the user does not have access to the job
     */
    public function getJobFlashcards(int $jobId, int $page = 1, int $perPage = 20): AIJobFlashcardsResponseDTO;

    /**
     * Updates a single flashcard's status and optionally its content.
     *
     * @throws FlashcardNotFoundException if the flashcard does not exist
     * @throws AccessDeniedException if the user does not have access to the flashcard
     * @throws InvalidStatusTransitionException if the status transition is not allowed
     */
    public function updateFlashcard(int $flashcardId, UpdateFlashcardDTO $dto): FlashcardResponseDTO;

    /**
     * Performs a bulk operation (save/reject) on all flashcards for a specific job.
     *
     * @throws JobNotFoundException if the job does not exist
     * @throws AccessDeniedException if the user does not have access to the job
     * @throws DeckNotFoundException if the deck does not exist (when action is save)
     * @throws NoFlashcardsToProcessException if there are no flashcards to process
     */
    public function bulkSaveFlashcards(int $jobId, BulkSaveFlashcardsDTO $dto): void;
} 