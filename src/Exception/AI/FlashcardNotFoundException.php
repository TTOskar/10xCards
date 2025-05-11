<?php

declare(strict_types=1);

namespace App\Exception\AI;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class FlashcardNotFoundException extends NotFoundHttpException
{
    public function __construct(int $flashcardId)
    {
        parent::__construct(sprintf('Flashcard with ID %d not found', $flashcardId));
    }
} 