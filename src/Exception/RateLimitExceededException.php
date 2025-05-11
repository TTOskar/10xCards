<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class RateLimitExceededException extends TooManyRequestsHttpException
{
    public function __construct(string $message = 'Rate limit exceeded', \Throwable $previous = null, int $retryAfter = 60, array $headers = [])
    {
        parent::__construct($retryAfter, $message, $previous, 429, $headers);
    }
} 