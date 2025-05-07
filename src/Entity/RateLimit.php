<?php

namespace App\Entity;

use App\Repository\RateLimitRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RateLimitRepository::class)]
#[ORM\Table(name: 'rate_limit', schema: 'app')]
class RateLimit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?DateTimeImmutable $windowStart = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private int $requestCount = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private int $textCharacters = 0;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private int $flashcardCount = 0;

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

    public function getWindowStart(): ?DateTimeImmutable
    {
        return $this->windowStart;
    }

    public function setWindowStart(DateTimeImmutable $windowStart): static
    {
        $this->windowStart = $windowStart;
        return $this;
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    public function setRequestCount(int $count): static
    {
        $this->requestCount = $count;
        return $this;
    }

    public function incrementRequestCount(): static
    {
        $this->requestCount++;
        return $this;
    }

    public function getTextCharacters(): int
    {
        return $this->textCharacters;
    }

    public function setTextCharacters(int $characters): static
    {
        $this->textCharacters = $characters;
        return $this;
    }

    public function addTextCharacters(int $characters): static
    {
        $this->textCharacters += $characters;
        return $this;
    }

    public function getFlashcardCount(): int
    {
        return $this->flashcardCount;
    }

    public function setFlashcardCount(int $count): static
    {
        $this->flashcardCount = $count;
        return $this;
    }

    public function incrementFlashcardCount(int $count = 1): static
    {
        $this->flashcardCount += $count;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('Rate Limit for %s (%s)', $this->user, $this->windowStart->format('Y-m-d H:i:s'));
    }
} 