<?php

declare(strict_types=1);

namespace App\Entity\AI;

use App\Enum\AI\JobStatus;
use App\Repository\AI\AiJobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AiJobRepository::class)]
#[ORM\Table(name: 'app.ai_jobs')]
#[ORM\Index(columns: ['user_id'])]
class AiJob
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $userId;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $inputTextLength;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $tokenCount;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $flashcardsCount;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $durationMs;

    #[ORM\Column(length: 50, enumType: JobStatus::class)]
    private JobStatus $status;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, AiJobFlashcard> */
    #[ORM\OneToMany(
        mappedBy: 'aiJob',
        targetEntity: AiJobFlashcard::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $flashcards;

    public function __construct(
        ?int $userId,
        int $inputTextLength,
        int $tokenCount,
        int $flashcardsCount,
        int $durationMs,
        JobStatus $status = JobStatus::PENDING,
    ) {
        $this->userId = $userId;
        $this->inputTextLength = $inputTextLength;
        $this->tokenCount = $tokenCount;
        $this->flashcardsCount = $flashcardsCount;
        $this->durationMs = $durationMs;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
        $this->flashcards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getInputTextLength(): int
    {
        return $this->inputTextLength;
    }

    public function getTokenCount(): int
    {
        return $this->tokenCount;
    }

    public function getFlashcardsCount(): int
    {
        return $this->flashcardsCount;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function getStatus(): JobStatus
    {
        return $this->status;
    }

    public function setStatus(JobStatus $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, AiJobFlashcard>
     */
    public function getFlashcards(): Collection
    {
        return $this->flashcards;
    }

    public function addFlashcard(string $front, string $back): void
    {
        $flashcard = new AiJobFlashcard($this, $front, $back);
        $this->flashcards->add($flashcard);
    }

    public function isCompleted(): bool
    {
        return $this->status === JobStatus::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === JobStatus::PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === JobStatus::FAILED;
    }

    public function complete(): void
    {
        $this->status = JobStatus::COMPLETED;
    }

    public function fail(): void
    {
        $this->status = JobStatus::FAILED;
    }
} 