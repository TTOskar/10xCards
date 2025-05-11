<?php

declare(strict_types=1);

namespace App\DTO\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Pagination information',
    required: ['page', 'perPage', 'total']
)]
readonly class PaginationDTO
{
    public function __construct(
        #[OA\Property(description: 'Current page number (1-based)', minimum: 1)]
        public int $page,

        #[OA\Property(description: 'Number of items per page', minimum: 1, maximum: 100)]
        public int $perPage,

        #[OA\Property(description: 'Total number of items')]
        public int $total,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            page: $data['page'],
            perPage: $data['per_page'],
            total: $data['total'],
        );
    }
} 