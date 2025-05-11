<?php

declare(strict_types=1);

namespace App\Service\AI\Client;

use App\DTO\Response\AI\FlashcardDTO;

interface AiClientInterface
{
    /**
     * @return FlashcardDTO[]
     */
    public function generateFlashcards(string $inputText): array;

    /**
     * Returns the number of tokens used in the last request.
     */
    public function getLastRequestTokenCount(): int;
} 