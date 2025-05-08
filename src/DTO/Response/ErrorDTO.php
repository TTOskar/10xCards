<?php

namespace App\DTO\Response;

class ErrorDTO
{
    /**
     * @param string $message
     * @param array<string, string[]> $errors
     */
    public function __construct(
        public readonly string $message,
        public readonly array $errors = []
    ) {
    }
} 