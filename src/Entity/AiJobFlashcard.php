<?php

namespace App\Entity;

use App\Repository\AiJobFlashcardRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AiJobFlashcardRepository::class)]
#[ORM\Table(name: 'ai_job_flashcards', schema: 'app')]
#[ORM\HasLifecycleCallbacks]
class AiJobFlashcard
{
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_EDITED = 'edited';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'flashcards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AiJob $aiJob = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private ?string $front = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    private ?string $back = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::STATUS_ACCEPTED, self::STATUS_EDITED, self::STATUS_REJECTED])]
    private string $status = self::STATUS_ACCEPTED;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $editedFront = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $editedBack = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAiJob(): ?AiJob
    {
        return $this->aiJob;
    }

    public function setAiJob(?AiJob $aiJob): static
    {
        $this->aiJob = $aiJob;
        return $this;
    }

    public function getFront(): ?string
    {
        return $this->front;
    }

    public function setFront(string $front): static
    {
        $this->front = $front;
        return $this;
    }

    public function getBack(): ?string
    {
        return $this->back;
    }

    public function setBack(string $back): static
    {
        $this->back = $back;
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

    public function getEditedFront(): ?string
    {
        return $this->editedFront;
    }

    public function setEditedFront(?string $editedFront): static
    {
        $this->editedFront = $editedFront;
        return $this;
    }

    public function getEditedBack(): ?string
    {
        return $this->editedBack;
    }

    public function setEditedBack(?string $editedBack): static
    {
        $this->editedBack = $editedBack;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return substr($this->front, 0, 30) . '...';
    }

    /**
     * Returns the current front text (edited if available, original otherwise)
     */
    public function getCurrentFront(): string
    {
        return $this->editedFront ?? $this->front;
    }

    /**
     * Returns the current back text (edited if available, original otherwise)
     */
    public function getCurrentBack(): string
    {
        return $this->editedBack ?? $this->back;
    }
} 