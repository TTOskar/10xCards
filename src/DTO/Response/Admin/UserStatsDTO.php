<?php

namespace App\DTO\Response\Admin;

class UserStatsDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $totalDecks,
        public readonly int $totalCards,
        public readonly int $aiJobsCount,
        public readonly int $generatedFlashcardsCount,
        public readonly int $studySessionsCount
    ) {
    }
} 