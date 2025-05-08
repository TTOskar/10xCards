<?php

namespace App\DTO\Request\Deck;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateDeckDTO
{
    public function __construct(
        #[Assert\Length(max: 100)]
        public readonly ?string $name = null,
        
        #[Assert\Length(max: 1000)]
        public readonly ?string $description = null
    ) {
    }
} 