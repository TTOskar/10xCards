<?php

namespace App\DTO\Response;

class PaginationDTO
{
    public function __construct(
        public readonly int $page,
        public readonly int $limit,
        public readonly int $total
    ) {
    }
} 