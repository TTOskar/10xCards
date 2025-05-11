<?php

declare(strict_types=1);

namespace App\DTO\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Error response',
    required: ['message', 'code']
)]
readonly class Error
{
    public function __construct(
        #[OA\Property(description: 'Error message')]
        public string $message,

        #[OA\Property(description: 'HTTP status code', minimum: 400, maximum: 599)]
        public int $code,
    ) {
    }
} 