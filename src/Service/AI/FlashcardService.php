<?php

declare(strict_types=1);

namespace App\Service\AI;

use App\DTO\Request\AI\BulkSaveFlashcardsDTO;
use App\DTO\Request\AI\UpdateFlashcardDTO;
use App\DTO\Response\AI\AIJobFlashcardsResponseDTO;
use App\DTO\Response\AI\FlashcardResponseDTO;
use App\DTO\Response\PaginationDTO;
use App\Entity\Card;
use App\Enum\AI\FlashcardStatus;
use App\Enum\AI\BulkAction;
use App\Exception\AI\FlashcardNotFoundException;
use App\Exception\AI\InvalidStatusTransitionException;
use App\Exception\AI\JobNotFoundException;
use App\Exception\AI\NoFlashcardsToProcessException;
use App\Exception\Deck\DeckNotFoundException;
use App\Repository\AI\AiJobFlashcardRepository;
use App\Repository\AI\AiJobRepository;
use App\Repository\CardRepository;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class FlashcardService implements FlashcardServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AiJobRepository $aiJobRepository,
        private AiJobFlashcardRepository $aiJobFlashcardRepository,
        private CardRepository $cardRepository,
        private DeckRepository $deckRepository,
        private Security $security,
    ) {
    }

    public function getJobFlashcards(int $jobId, int $page = 1, int $perPage = 20): AIJobFlashcardsResponseDTO
    {
        $job = $this->aiJobRepository->find(id: $jobId);
        if (!$job) {
            throw new JobNotFoundException(jobId: $jobId);
        }

        if ($job->getUserId() !== $this->security->getUser()->getId()) {
            throw new AccessDeniedHttpException(message: 'You do not have access to this job');
        }

        $flashcards = $this->aiJobFlashcardRepository->findPaginatedByJob($job, $page, $perPage);
        $total = $this->aiJobFlashcardRepository->countByJob($job);

        return new AIJobFlashcardsResponseDTO(
            jobId: $job->getId(),
            jobStatus: $job->getStatus(),
            flashcards: array_map(
                callback: fn ($flashcard): FlashcardResponseDTO => FlashcardResponseDTO::fromArray(data: [
                    'id' => $flashcard->getId(),
                    'front' => $flashcard->getFront(),
                    'back' => $flashcard->getBack(),
                    'status' => $flashcard->getStatus()->value,
                    'edited_front' => $flashcard->getEditedFront(),
                    'edited_back' => $flashcard->getEditedBack(),
                    'created_at' => $flashcard->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => null,
                ]),
                array: $flashcards
            ),
            pagination: new PaginationDTO(
                page: $page,
                perPage: $perPage,
                total: $total,
            ),
            createdAt: $job->getCreatedAt(),
            completedAt: null,
        );
    }

    public function updateFlashcard(int $flashcardId, UpdateFlashcardDTO $dto): FlashcardResponseDTO
    {
        $flashcard = $this->aiJobFlashcardRepository->find($flashcardId);
        if (!$flashcard) {
            throw new FlashcardNotFoundException($flashcardId);
        }

        if ($flashcard->getAiJob()->getUserId() !== $this->security->getUser()->getId()) {
            throw new AccessDeniedHttpException('You do not have access to this flashcard');
        }

        $currentStatus = $flashcard->getStatus();
        if (!$currentStatus->isModifiable()) {
            throw new InvalidStatusTransitionException($currentStatus, FlashcardStatus::from($dto->status->value));
        }

        $flashcard->setStatus(FlashcardStatus::from($dto->status->value));
        if ($dto->editedFront !== null) {
            $flashcard->setEditedFront($dto->editedFront);
        }
        if ($dto->editedBack !== null) {
            $flashcard->setEditedBack($dto->editedBack);
        }

        $this->entityManager->flush();

        return FlashcardResponseDTO::fromArray([
            'id' => $flashcard->getId(),
            'front' => $flashcard->getFront(),
            'back' => $flashcard->getBack(),
            'status' => $flashcard->getStatus()->value,
            'edited_front' => $flashcard->getEditedFront(),
            'edited_back' => $flashcard->getEditedBack(),
            'created_at' => $flashcard->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => null,
        ]);
    }

    public function bulkSaveFlashcards(int $jobId, BulkSaveFlashcardsDTO $dto): void
    {
        $job = $this->aiJobRepository->find($jobId);
        if (!$job) {
            throw new JobNotFoundException($jobId);
        }

        if ($job->getUserId() !== $this->security->getUser()->getId()) {
            throw new AccessDeniedHttpException('You do not have access to this job');
        }

        $flashcards = $this->aiJobFlashcardRepository->findByJobAndStatus($job, FlashcardStatus::PENDING);
        if (empty($flashcards)) {
            throw new NoFlashcardsToProcessException($jobId);
        }

        if ($dto->action === BulkAction::SAVE) {
            if (!$dto->deckId) {
                throw new \InvalidArgumentException('Deck ID is required for save action');
            }

            $deck = $this->deckRepository->find($dto->deckId);
            if (!$deck) {
                throw new DeckNotFoundException($dto->deckId);
            }

            if ($deck->getUser()->getId() !== $this->security->getUser()->getId()) {
                throw new AccessDeniedHttpException('You do not have access to this deck');
            }

            $this->entityManager->beginTransaction();
            try {
                foreach ($flashcards as $flashcard) {
                    $card = new Card();
                    $card->setDeck($deck);
                    $card->setFront($flashcard->getCurrentFront());
                    $card->setBack($flashcard->getCurrentBack());
                    $card->setSource(Card::SOURCE_AI);
                    
                    $flashcard->accept();
                    
                    $this->entityManager->persist($card);
                }
                $this->entityManager->flush();
                $this->entityManager->commit();
            } catch (\Throwable $e) {
                $this->entityManager->rollback();
                throw $e;
            }
        } else {
            foreach ($flashcards as $flashcard) {
                $flashcard->reject();
            }
            $this->entityManager->flush();
        }
    }
} 