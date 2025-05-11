<?php

declare(strict_types=1);

namespace App\Service\AI;

use App\DTO\Request\AI\GenerateFlashcardsRequest;
use App\DTO\Response\AI\GenerateFlashcardsResponse;

interface AiFlashcardsGeneratorInterface
{
    /**
     * Generates flashcards from the provided input text using AI.
     * This method is rate limited to 5 requests per minute per user.
     */
    public function generate(GenerateFlashcardsRequest $request): GenerateFlashcardsResponse;
} 