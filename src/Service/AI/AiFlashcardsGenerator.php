<?php

declare(strict_types=1);

namespace App\Service\AI;

use App\DTO\Request\AI\GenerateFlashcardsRequest;
use App\DTO\Response\AI\GenerateFlashcardsResponse;
use App\Exception\RateLimitExceededException;
use App\Service\AI\Client\AiClientInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AiFlashcardsGenerator implements AiFlashcardsGeneratorInterface
{
    public function __construct(
        private readonly RateLimiterFactory $aiGenerateFlashcardsLimiter,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AiClientInterface $aiClient,
    ) {
    }

    public function generate(GenerateFlashcardsRequest $request): GenerateFlashcardsResponse
    {
        $limiter = $this->aiGenerateFlashcardsLimiter->create(
            $this->tokenStorage->getToken()?->getUserIdentifier() ?? 'anonymous'
        );

        if ($limiter->consume()->isAccepted() === false) {
            throw new RateLimitExceededException(
                'Rate limit exceeded. Please try again in a minute.'
            );
        }

        $inputText = $request->getInputText();
        $startTime = microtime(true);
        
        $flashcards = $this->aiClient->generateFlashcards($inputText);
        
        return new GenerateFlashcardsResponse(
            id: random_int(1, 1000), // Will be replaced with actual DB id
            inputTextLength: strlen($inputText),
            tokenCount: $this->aiClient->getLastRequestTokenCount(),
            flashcardsCount: count($flashcards),
            durationMs: (int) ((microtime(true) - $startTime) * 1000),
            createdAt: new \DateTimeImmutable(),
            flashcards: $flashcards
        );
    }
} 