<?php

namespace App\DTO\Request\Deck;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDeckDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public readonly string $name,
        
        #[Assert\Length(max: 1000)]
        public readonly ?string $description = null
    ) {
    }
} 