<?php

namespace App\DTO\Request\Card;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCardDTO
{
    public function __construct(
        #[Assert\Length(max: 200)]
        public readonly ?string $front = null,
        
        #[Assert\Length(max: 1000)]
        public readonly ?string $back = null
    ) {
    }
} 