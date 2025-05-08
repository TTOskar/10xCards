<?php

namespace App\DTO\Request\AI;

use Symfony\Component\Validator\Constraints as Assert;

class GenerateFlashcardsDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 10000)]
        public readonly string $inputText
    ) {
    }
} 