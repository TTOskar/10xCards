<?php

namespace App\DTO\Response\Admin;

class KpiDTO
{
    /**
     * @param array<string, mixed> $globalStats
     * @param UserStatsDTO[] $userStats
     */
    public function __construct(
        public readonly array $globalStats,
        public readonly array $userStats
    ) {
    }
} 