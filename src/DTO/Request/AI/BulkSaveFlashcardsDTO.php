<?php

declare(strict_types=1);

namespace App\DTO\Request\AI;

use App\Enum\AI\BulkAction;
use Symfony\Component\Validator\Constraints as Assert;

readonly class BulkSaveFlashcardsDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'Action cannot be null')]
        public BulkAction $action,

        #[Assert\Positive(message: 'Deck ID must be a positive number')]
        #[Assert\NotNull(message: 'Deck ID is required when action is save', groups: ['save'])]
        public ?int $deckId = null,
    ) {
    }
} 