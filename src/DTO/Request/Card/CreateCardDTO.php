<?php

namespace App\DTO\Request\Card;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCardDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $deckId,
        
        #[Assert\NotBlank]
        #[Assert\Length(max: 200)]
        public readonly string $front,
        
        #[Assert\NotBlank]
        #[Assert\Length(max: 1000)]
        public readonly string $back,
        
        #[Assert\Choice(choices: ['manual', 'ai'])]
        public readonly string $source = 'manual'
    ) {
    }
} 