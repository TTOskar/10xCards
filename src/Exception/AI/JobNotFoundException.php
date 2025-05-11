<?php

declare(strict_types=1);

namespace App\Exception\AI;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class JobNotFoundException extends NotFoundHttpException
{
    public function __construct(int $jobId)
    {
        parent::__construct(sprintf('AI Job with ID %d not found', $jobId));
    }
} 