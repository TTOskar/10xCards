<?php

namespace App\Service;

use App\Repository\AI\AiJobFlashcardRepository;
use App\Repository\AI\AiJobRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserLimitsService
{
    private const MAX_CHARS = 10000;
    private const MAX_REQUESTS_PER_MINUTE = 5;
    private const MAX_FLASHCARDS_PER_DAY = 500;

    public function __construct(
        private readonly Security $security,
        private readonly AiJobFlashcardRepository $flashcardRepository,
        private readonly AiJobRepository $jobRepository
    ) {
    }

    public function getUserLimits(): array
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \RuntimeException('User must be logged in to check limits');
        }

        $todayFlashcards = $this->flashcardRepository->countTodayFlashcardsForUser($user->getId());
        $recentRequests = $this->jobRepository->countRecentRequestsForUser($user->getId());

        return [
            'remainingChars' => self::MAX_CHARS,
            'remainingRequestsPerMin' => self::MAX_REQUESTS_PER_MINUTE - $recentRequests,
            'remainingFlashcardsToday' => self::MAX_FLASHCARDS_PER_DAY - $todayFlashcards,
        ];
    }

    public function checkLimits(string $text): void
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \RuntimeException('User must be logged in to generate flashcards');
        }

        $textLength = mb_strlen($text);
        if ($textLength > self::MAX_CHARS) {
            throw new \RuntimeException(sprintf(
                'Text is too long. Maximum %d characters allowed, got %d',
                self::MAX_CHARS,
                $textLength
            ));
        }

        $recentRequests = $this->jobRepository->countRecentRequestsForUser($user->getId());
        if ($recentRequests >= self::MAX_REQUESTS_PER_MINUTE) {
            throw new \RuntimeException(sprintf(
                'Too many requests. Maximum %d requests per minute allowed',
                self::MAX_REQUESTS_PER_MINUTE
            ));
        }

        $todayFlashcards = $this->flashcardRepository->countTodayFlashcardsForUser($user->getId());
        if ($todayFlashcards >= self::MAX_FLASHCARDS_PER_DAY) {
            throw new \RuntimeException(sprintf(
                'Daily limit reached. Maximum %d flashcards per day allowed',
                self::MAX_FLASHCARDS_PER_DAY
            ));
        }
    }
} 