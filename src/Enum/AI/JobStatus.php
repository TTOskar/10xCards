<?php

declare(strict_types=1);

namespace App\Enum\AI;

enum JobStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function isFinished(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED], true);
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }
} 