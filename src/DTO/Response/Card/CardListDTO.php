<?php

namespace App\DTO\Response\Card;

use App\DTO\Response\PaginationDTO;

class CardListDTO
{
    /**
     * @param CardDTO[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationDTO $pagination
    ) {
    }
} 