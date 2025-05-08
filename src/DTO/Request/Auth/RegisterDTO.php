<?php

namespace App\DTO\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[Assert\Length(max: 255)]
        public readonly string $email,
        
        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 255)]
        public readonly string $password
    ) {
    }
} 