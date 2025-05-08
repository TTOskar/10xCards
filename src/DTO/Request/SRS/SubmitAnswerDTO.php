<?php

namespace App\DTO\Request\SRS;

use App\Enum\StudyResult;
use Symfony\Component\Validator\Constraints as Assert;

class SubmitAnswerDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public readonly int $cardId,
        
        #[Assert\NotNull]
        public readonly StudyResult $result
    ) {
    }
} 