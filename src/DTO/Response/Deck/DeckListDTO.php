<?php

namespace App\DTO\Response\Deck;

use App\DTO\Response\PaginationDTO;

class DeckListDTO
{
    /**
     * @param DeckDTO[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationDTO $pagination
    ) {
    }
} 