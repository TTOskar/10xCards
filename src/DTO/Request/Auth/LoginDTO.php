<?php

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class LoginDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,
        
        #[Assert\NotBlank]
        public readonly string $password
    ) {
    }
} 