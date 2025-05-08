<?php

namespace App\DTO\Response\Admin;

use App\Enum\UserRole;

class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly UserRole $role,
        public readonly int $failedLoginAttempts,
        public readonly ?\DateTimeInterface $lockedUntil,
        public readonly \DateTimeImmutable $createdAt,
        public readonly bool $isDeleted,
        public readonly ?\DateTimeInterface $deletionRequestedAt,
        public readonly ?\DateTimeInterface $deletedAt
    ) {
    }
} 