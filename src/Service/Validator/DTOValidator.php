<?php

namespace App\Service\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly array $validationGroups
    ) {
    }

    public function validate(object $dto, ?string $context = null): array
    {
        $groups = isset($context) && isset($this->validationGroups[$context])
            ? $this->validationGroups[$context]
            : ['Default'];

        $violations = $this->validator->validate($dto, null, $groups);
        
        $errors = [];
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath][] = $violation->getMessage();
        }

        return $errors;
    }
} 