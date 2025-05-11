<?php

declare(strict_types=1);

namespace App\Exception\Deck;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeckNotFoundException extends NotFoundHttpException
{
    public function __construct(int $deckId)
    {
        parent::__construct(sprintf('Deck with ID %d not found', $deckId));
    }
} 