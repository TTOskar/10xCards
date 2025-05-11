<?php

declare(strict_types=1);

namespace App\DTO\Response\AI;

final class FlashcardDTO
{
    public function __construct(
        private readonly string $front,
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