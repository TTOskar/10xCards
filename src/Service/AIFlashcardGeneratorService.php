<?php

namespace App\Service;

use App\Entity\AI\AiJob;
use App\Entity\AI\AiJobFlashcard;
use App\Enum\AI\FlashcardStatus;
use App\Enum\AI\JobStatus;
use App\Repository\AI\AiJobFlashcardRepository;
use App\Repository\AI\AiJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIFlashcardGeneratorService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly UserLimitsService $limitsService,
        private readonly AiJobFlashcardRepository $flashcardRepository,
        private readonly AiJobRepository $jobRepository,
        private readonly string $aiApiEndpoint,
        private readonly string $aiApiKey
    ) {
    }

    public function generateFromText(string $text): array
    {
        // Check user limits before making the API call
        $this->limitsService->checkLimits($text);

        // Create a new job
        $job = new AiJob(
            userId: $this->security->getUser()->getId(),
            inputTextLength: mb_strlen($text),
            tokenCount: 0, // Will be updated after API call
            flashcardsCount: 0, // Will be updated after API call
            durationMs: 0, // Will be updated after API call
            status: JobStatus::PROCESSING
        );
        
        $this->entityManager->persist($job);
        $this->entityManager->flush();

        try {
            $startTime = microtime(true);

            // Call AI API to generate flashcards
            $response = $this->httpClient->request('POST', $this->aiApiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->aiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'text' => $text,
                    'max_flashcards' => 10, // Configurable
                ],
            ]);

            $data = $response->toArray();
            $endTime = microtime(true);

            // Create flashcards from API response
            $flashcards = [];
            foreach ($data['flashcards'] as $flashcardData) {
                $flashcard = new AiJobFlashcard(
                    aiJob: $job,
                    front: $flashcardData['front'],
                    back: $flashcardData['back'],
                    status: FlashcardStatus::PENDING
                );

                $this->entityManager->persist($flashcard);
                $flashcards[] = [
                    'id' => $flashcard->getId(),
                    'front' => $flashcard->getFront(),
                    'back' => $flashcard->getBack(),
                    'status' => $flashcard->getStatus()->value,
                ];
            }

            // Update job with final stats
            $job->setTokenCount($data['token_count'] ?? 0);
            $job->setFlashcardsCount(count($flashcards));
            $job->setDurationMs((int)($endTime - $startTime) * 1000);
            $job->complete();

            $this->entityManager->flush();

            return [
                'jobId' => $job->getId(),
                'flashcards' => $flashcards,
            ];
        } catch (\Exception $e) {
            $job->fail();
            $this->entityManager->flush();

            throw $e;
        }
    }

    public function updateFlashcardStatus(int $flashcardId, FlashcardStatus $status, ?array $editedData = null): void
    {
        $flashcard = $this->flashcardRepository->find($flashcardId);
        if (!$flashcard) {
            throw new \RuntimeException('Flashcard not found');
        }

        if ($flashcard->getAiJob()->getUserId() !== $this->security->getUser()->getId()) {
            throw new \RuntimeException('Access denied');
        }

        if (!$flashcard->getStatus()->isModifiable()) {
            throw new \RuntimeException('Flashcard status cannot be modified');
        }

        $flashcard->setStatus($status);
        
        if ($status === FlashcardStatus::EDITED && $editedData) {
            $flashcard->setEditedFront($editedData['front']);
            $flashcard->setEditedBack($editedData['back']);
        }

        $this->entityManager->flush();
    }

    public function bulkSave(int $jobId, array $flashcardIds): void
    {
        $job = $this->jobRepository->find($jobId);
        if (!$job) {
            throw new \RuntimeException('Job not found');
        }

        if ($job->getUserId() !== $this->security->getUser()->getId()) {
            throw new \RuntimeException('Access denied');
        }

        $flashcards = $this->flashcardRepository->findBy([
            'id' => $flashcardIds,
            'aiJob' => $job,
        ]);

        foreach ($flashcards as $flashcard) {
            if ($flashcard->getStatus()->isModifiable()) {
                $flashcard->accept();
            }
        }

        $this->entityManager->flush();
    }
} 