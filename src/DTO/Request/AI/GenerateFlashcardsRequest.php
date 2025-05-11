<?php

declare(strict_types=1);

namespace App\DTO\Request\AI;

use Symfony\Component\Validator\Constraints as Assert;

final class GenerateFlashcardsRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Input text cannot be empty')]
        #[Assert\Length(
            min: 1000,
            max: 10000,
            minMessage: 'Input text cannot be shorter than {{ limit }} characters',
            maxMessage: 'Input text cannot be longer than {{ limit }} characters'
        )]
        private readonly string $inputText
    ) {
    }

    public function getInputText(): string
    {
        return $this->inputText;
    }
} 