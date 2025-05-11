<?php

declare(strict_types=1);

namespace App\Exception\AI;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class NoFlashcardsToProcessException extends BadRequestHttpException
{
    public function __construct(int $jobId)
    {
        parent::__construct(
            sprintf('No flashcards to process for AI Job with ID %d', $jobId)
        );
    }
} 