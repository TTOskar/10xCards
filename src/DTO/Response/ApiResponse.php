<?php

namespace App\DTO\Response;

class ApiResponse
{
    public function __construct(
        public readonly mixed $data,
        public readonly bool $success = true,
        public readonly ?string $message = null,
        public readonly ?array $meta = null
    ) {
    }
} 