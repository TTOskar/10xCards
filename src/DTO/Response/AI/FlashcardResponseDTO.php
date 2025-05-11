<?php 

declare(strict_types=1);

namespace App\DTO\Response\AI;

use App\Enum\AI\FlashcardStatus;
use OpenApi\Attributes as OA;

#[OA\Schema(
    description: 'Response containing flashcard data',
    required: ['id', 'front', 'back', 'status', 'createdAt']
)]
readonly class FlashcardResponseDTO
{
    public function __construct(
        #[OA\Property(description: 'Flashcard ID')]
        public int $id,

        #[OA\Property(description: 'Front side content', maxLength: 200)]
        public string $front,

        #[OA\Property(description: 'Back side content', maxLength: 1000)]
        public string $back,

        #[OA\Property(description: 'Current status', enum: ['pending', 'accepted', 'rejected'])]
        public FlashcardStatus $status,

        #[OA\Property(description: 'Edited front content', maxLength: 200, nullable: true)]
        public ?string $editedFront,

        #[OA\Property(description: 'Edited back content', maxLength: 1000, nullable: true)]
        public ?string $editedBack,

        #[OA\Property(description: 'Creation timestamp', format: 'date-time')]
        public \DateTimeImmutable $createdAt,

        #[OA\Property(description: 'Last update timestamp', format: 'date-time', nullable: true)]
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            front: $data['front'],
            back: $data['back'],
            status: FlashcardStatus::from($data['status']),
            editedFront: $data['edited_front'] ?? null,
            editedBack: $data['edited_back'] ?? null,
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }
} 