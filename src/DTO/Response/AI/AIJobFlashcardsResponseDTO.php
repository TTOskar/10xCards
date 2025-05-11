<?php

declare(strict_types=1);

namespace App\DTO\Response\AI;

use App\DTO\Response\PaginationDTO;
use App\Enum\AI\JobStatus;
use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Response containing job flashcards with pagination',
    required: ['jobId', 'jobStatus', 'flashcards', 'pagination', 'createdAt']
)]
readonly class AIJobFlashcardsResponseDTO
{
    /**
     * @param FlashcardResponseDTO[] $flashcards
     */
    public function __construct(
        #[OA\Property(description: 'AI Job ID')]
        public int $jobId,

        #[OA\Property(description: 'Current job status', enum: ['pending', 'processing', 'completed', 'failed'])]
        public JobStatus $jobStatus,

        #[OA\Property(
            description: 'List of flashcards',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/FlashcardResponse')
        )]
        public array $flashcards,

        #[OA\Property(ref: '#/components/schemas/Pagination')]
        public PaginationDTO $pagination,

        #[OA\Property(description: 'Job creation timestamp', format: 'date-time')]
        public \DateTimeImmutable $createdAt,

        #[OA\Property(description: 'Job completion timestamp', format: 'date-time', nullable: true)]
        public ?\DateTimeImmutable $completedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            jobId: $data['job_id'],
            jobStatus: JobStatus::from($data['job_status']),
            flashcards: array_map(
                fn (array $flashcard) => FlashcardResponseDTO::fromArray($flashcard),
                $data['flashcards']
            ),
            pagination: PaginationDTO::fromArray($data['pagination']),
            createdAt: new \DateTimeImmutable($data['created_at']),
            completedAt: isset($data['completed_at']) ? new \DateTimeImmutable($data['completed_at']) : null,
        );
    }
} 