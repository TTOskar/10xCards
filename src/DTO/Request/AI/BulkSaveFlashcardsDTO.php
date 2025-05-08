<?php

namespace App\DTO\Request\AI;

use Symfony\Component\Validator\Constraints as Assert;

class BulkSaveFlashcardsDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $deckId
    ) {
    }
} 