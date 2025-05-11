<?php

declare(strict_types=1);

namespace App\DTO\Request\AI;

use App\Enum\AI\FlashcardStatus;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateFlashcardDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'Status cannot be null')]
        public FlashcardStatus $status,

        #[Assert\Length(max: 200, maxMessage: 'Front content cannot be longer than {{ limit }} characters')]
        public ?string $editedFront = null,

        #[Assert\Length(max: 1000, maxMessage: 'Back content cannot be longer than {{ limit }} characters')]
        public ?string $editedBack = null,
    ) {
    }
} 