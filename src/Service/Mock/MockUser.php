<?php

namespace App\Service\Mock;

use Symfony\Component\Security\Core\User\UserInterface;

class MockUser implements UserInterface
{
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return 'mock_user';
    }

    public function getId(): int
    {
        return 1;
    }
} 