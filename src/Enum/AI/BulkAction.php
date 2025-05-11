<?php

declare(strict_types=1);

namespace App\Enum\AI;

enum BulkAction: string
{
    case SAVE = 'save';
    case REJECT = 'reject';

    public function requiresDeckId(): bool
    {
        return $this === self::SAVE;
    }

    public function label(): string
    {
        return match($this) {
            self::SAVE => 'Save to Deck',
            self::REJECT => 'Reject All',
        };
    }
} 