<?php

declare(strict_types=1);

namespace App\Exception\AI;

use App\Enum\AI\FlashcardStatus;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class InvalidStatusTransitionException extends BadRequestHttpException
{
    public function __construct(FlashcardStatus $currentStatus, FlashcardStatus $newStatus)
    {
        parent::__construct(
            sprintf(
                'Cannot transition flashcard from status "%s" to "%s"',
                $currentStatus->value,
                $newStatus->value
            )
        );
    }
} 