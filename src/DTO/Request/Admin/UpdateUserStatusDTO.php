<?php

namespace App\DTO\Request\Admin;

use App\Enum\UserAction;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserStatusDTO
{
    public function __construct(
        #[Assert\NotNull]
        public readonly UserAction $action
    ) {
    }
} 