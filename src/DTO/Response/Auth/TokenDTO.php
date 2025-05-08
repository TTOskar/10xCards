<?php

namespace App\DTO\Response\Auth;

class TokenDTO
{
    public function __construct(
        public readonly string $token,
        public readonly \DateTimeImmutable $expiresAt
    ) {
    }
} 