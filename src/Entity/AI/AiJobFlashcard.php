<?php

declare(strict_types=1);

namespace App\Entity\AI;

use App\Enum\AI\AiJobFlashcardStatus;
use App\Repository\AI\AiJobFlashcardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AiJobFlashcardRepository::class)]
#[ORM\Table(name: 'app.ai_job_flashcards')]
#[ORM\Index(columns: ['ai_job_id'])]
class AiJobFlashcard
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'flashcards')]
    #[ORM\JoinColumn(name: 'ai_job_id', nullable: false)]
    private AiJob $aiJob;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private string $front;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    private string $back;

    #[ORM\Column(length: 50, enumType: AiJobFlashcardStatus::class)]
    private AiJobFlashcardStatus $status;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200)]
    private ?string $editedFront = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $editedBack = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        AiJob $aiJob,
        string $front,
        string $back,
        AiJobFlashcardStatus $status = AiJobFlashcardStatus::ACCEPTED,
    ) {
        $this->aiJob = $aiJob;
        $this->front = $front;
        $this->back = $back;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAiJob(): AiJob
    {
        return $this->aiJob;
    }

    public function getFront(): string
    {
        return $this->front;
    }

    public function getBack(): string
    {
        return $this->back;
    }

    public function getStatus(): AiJobFlashcardStatus
    {
        return $this->status;
    }

    public function setStatus(AiJobFlashcardStatus $status): void
    {
        $this->status = $status;
    }

    public function getEditedFront(): ?string
    {
        return $this->editedFront;
    }

    public function setEditedFront(?string $editedFront): void
    {
        if ($editedFront !== null && $editedFront !== $this->front) {
            $this->editedFront = $editedFront;
            $this->status = AiJobFlashcardStatus::EDITED;
        }
    }

    public function getEditedBack(): ?string
    {
        return $this->editedBack;
    }

    public function setEditedBack(?string $editedBack): void
    {
        if ($editedBack !== null && $editedBack !== $this->back) {
            $this->editedBack = $editedBack;
            $this->status = AiJobFlashcardStatus::EDITED;
        }
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCurrentFront(): string
    {
        return $this->editedFront ?? $this->front;
    }

    public function getCurrentBack(): string
    {
        return $this->editedBack ?? $this->back;
    }

    public function isAccepted(): bool
    {
        return $this->status === AiJobFlashcardStatus::ACCEPTED;
    }

    public function isEdited(): bool
    {
        return $this->status === AiJobFlashcardStatus::EDITED;
    }

    public function isRejected(): bool
    {
        return $this->status === AiJobFlashcardStatus::REJECTED;
    }

    public function accept(): void
    {
        $this->status = AiJobFlashcardStatus::ACCEPTED;
    }

    public function reject(): void
    {
        $this->status = AiJobFlashcardStatus::REJECTED;
    }
} 