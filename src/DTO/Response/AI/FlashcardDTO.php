<?php

declare(strict_types=1);

namespace App\DTO\Response\AI;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Single flashcard data'
)]
final class FlashcardDTO
{
    public function __construct(
        #[OA\Property(
            description: 'Front side of the flashcard (question)',
            type: 'string',
            maxLength: 200
        )]
        private readonly string $front,

        #[OA\Property(
            description: 'Back side of the flashcard (answer)',
            type: 'string',
            maxLength: 1000
        )]
        private readonly string $back
    ) {
    }

    public function getFront(): string
    {
        return $this->front;
    }

    public function getBack(): string
    {
        return $this->back;
    }
} 