<?php

namespace App\DTO\Request\AI;

use App\Enum\FlashcardStatus;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateFlashcardStatusDTO
{
    public function __construct(
        #[Assert\NotNull]
        public readonly FlashcardStatus $status,
        
        #[Assert\Length(max: 200)]
        public readonly ?string $editedFront = null,
        
        #[Assert\Length(max: 1000)]
        public readonly ?string $editedBack = null
    ) {
    }
} 