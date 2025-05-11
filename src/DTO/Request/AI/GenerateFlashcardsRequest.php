<?php

declare(strict_types=1);

namespace App\DTO\Request\AI;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    description: 'Request DTO for generating flashcards using AI',
    required: ['inputText']
)]
final class GenerateFlashcardsRequest
{
    public function __construct(
        #[OA\Property(
            description: 'Text to generate flashcards from',
            type: 'string',
            minLength: 1000,
            maxLength: 10000
        )]
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