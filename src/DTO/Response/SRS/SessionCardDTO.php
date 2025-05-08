<?php

namespace App\DTO\Response\SRS;

class SessionCardDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $front,
        public readonly string $back,
        public readonly \DateTimeInterface $dueDate
    ) {
    }
} 