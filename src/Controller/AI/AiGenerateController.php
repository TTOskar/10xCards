<?php

declare(strict_types=1);

namespace App\Controller\AI;

use App\DTO\Request\AI\GenerateFlashcardsRequest;
use App\DTO\Response\AI\GenerateFlashcardsResponse;
use App\Service\AI\AiFlashcardsGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class AiGenerateController extends AbstractController
{
    public function __construct(
        private readonly AiFlashcardsGeneratorInterface $flashcardsGenerator
    ) {
    }

    #[Route('/api/ai/generate', name: 'api_ai_generate_flashcards', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] GenerateFlashcardsRequest $request
    ): JsonResponse {
        $response = $this->flashcardsGenerator->generate($request);

        return $this->json($response);
    }
} 