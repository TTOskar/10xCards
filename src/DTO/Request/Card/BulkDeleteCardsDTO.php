<?php

namespace App\DTO\Request\Card;

use Symfony\Component\Validator\Constraints as Assert;

class BulkDeleteCardsDTO
{
    /**
     * @param int[] $ids
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\All([
            new Assert\Type('integer'),
            new Assert\Positive()
        ])]
        public readonly array $ids
    ) {
    }
} 