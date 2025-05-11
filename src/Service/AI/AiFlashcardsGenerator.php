<?php

declare(strict_types=1);

namespace App\Service\AI;

use App\DTO\Request\AI\GenerateFlashcardsRequest;
use App\DTO\Response\AI\GenerateFlashcardsResponse;
use App\Entity\AI\AiJob;
use App\Enum\AI\JobStatus;
use App\Exception\RateLimitExceededException;
use App\Repository\AI\AiJobRepository;
use App\Service\AI\Client\AiClientInterface;
use App\Service\AI\Mapper\AiFlashcardsMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AiFlashcardsGenerator implements AiFlashcardsGeneratorInterface
{
    public function __construct(
        private readonly RateLimiterFactory $aiGenerateFlashcardsLimiter,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly AiClientInterface $aiClient,
        private readonly AiJobRepository $aiJobRepository,
        private readonly AiFlashcardsMapper $flashcardsMapper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function generate(GenerateFlashcardsRequest $request): GenerateFlashcardsResponse
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token && is_numeric($token->getUserIdentifier()) 
            ? (int) $token->getUserIdentifier() 
            : null;

        $limiter = $this->aiGenerateFlashcardsLimiter->create($userId ? (string) $userId : 'anonymous');

        if ($limiter->consume()->isAccepted() === false) {
            $this->logger->warning('Rate limit exceeded for user {user}', [
                'user' => $userId ?? 'anonymous',
                'input_length' => strlen($request->getInputText()),
            ]);
            
            throw new RateLimitExceededException(
                'Rate limit exceeded. Please try again in a minute.'
            );
        }

        $inputText = $request->getInputText();
        $startTime = microtime(true);
        
        try {
            $this->logger->info('Starting flashcards generation for user {user}', [
                'user' => $userId ?? 'anonymous',
                'input_length' => strlen($inputText),
            ]);

            // Generate flashcards using AI
            $flashcards = $this->aiClient->generateFlashcards($inputText);
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            
            // Create and save AI job with flashcards
            $aiJob = new AiJob(
                userId: $userId,
                inputTextLength: strlen($inputText),
                tokenCount: $this->aiClient->getLastRequestTokenCount(),
                flashcardsCount: count($flashcards),
                durationMs: $durationMs,
                status: JobStatus::COMPLETED,
            );

            // Add flashcards to the job
            foreach ($flashcards as $flashcard) {
                $aiJob->addFlashcard($flashcard->getFront(), $flashcard->getBack());
            }

            // Save to database
            $this->aiJobRepository->save($aiJob);
            
            $this->logger->info('Successfully generated flashcards for user {user}', [
                'user' => $userId ?? 'anonymous',
                'job_id' => $aiJob->getId(),
                'flashcards_count' => $aiJob->getFlashcardsCount(),
                'duration_ms' => $aiJob->getDurationMs(),
                'token_count' => $aiJob->getTokenCount(),
            ]);
            
            return $this->flashcardsMapper->mapJobToResponse($aiJob);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to generate flashcards for user {user}: {error}', [
                'user' => $userId ?? 'anonymous',
                'error' => $e->getMessage(),
                'exception' => $e,
                'input_length' => strlen($inputText),
            ]);

            // Create failed job record
            $aiJob = new AiJob(
                userId: $userId,
                inputTextLength: strlen($inputText),
                tokenCount: 0,
                flashcardsCount: 0,
                durationMs: (int) ((microtime(true) - $startTime) * 1000),
                status: JobStatus::FAILED,
            );
            
            $this->aiJobRepository->save($aiJob);
            
            throw $e;
        }
    }
} 