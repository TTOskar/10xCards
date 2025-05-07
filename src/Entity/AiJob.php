<?php

namespace App\Entity;

use App\Repository\AiJobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AiJobRepository::class)]
#[ORM\Table(name: 'ai_jobs', schema: 'app')]
#[ORM\HasLifecycleCallbacks]
class AiJob
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $inputTextLength = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $tokenCount = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $flashcardsCount = 0;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $durationMs = 0;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_FAILED])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'aiJob', targetEntity: AiJobFlashcard::class, orphanRemoval: true)]
    private Collection $flashcards;

    public function __construct()
    {
        $this->flashcards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getInputTextLength(): int
    {
        return $this->inputTextLength;
    }

    public function setInputTextLength(int $length): static
    {
        $this->inputTextLength = $length;
        return $this;
    }

    public function getTokenCount(): int
    {
        return $this->tokenCount;
    }

    public function setTokenCount(int $count): static
    {
        $this->tokenCount = $count;
        return $this;
    }

    public function getFlashcardsCount(): int
    {
        return $this->flashcardsCount;
    }

    public function setFlashcardsCount(int $count): static
    {
        $this->flashcardsCount = $count;
        return $this;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function setDurationMs(int $duration): static
    {
        $this->durationMs = $duration;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
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

    public function addFlashcard(AiJobFlashcard $flashcard): static
    {
        if (!$this->flashcards->contains($flashcard)) {
            $this->flashcards->add($flashcard);
            $flashcard->setAiJob($this);
        }

        return $this;
    }

    public function removeFlashcard(AiJobFlashcard $flashcard): static
    {
        if ($this->flashcards->removeElement($flashcard)) {
            // set the owning side to null (unless already changed)
            if ($flashcard->getAiJob() === $this) {
                $flashcard->setAiJob(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('AI Job #%d (%s)', $this->id, $this->status);
    }
} 