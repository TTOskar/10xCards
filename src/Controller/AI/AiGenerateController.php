<?php

declare(strict_types=1);

namespace App\Controller\AI;

use App\DTO\Request\AI\GenerateFlashcardsRequest;
use App\DTO\Response\AI\GenerateFlashcardsResponse;
use App\Service\AI\AiFlashcardsGeneratorInterface;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/ai/generate',
        summary: 'Generate flashcards using AI',
        description: 'Generates flashcards from the provided text using AI. Limited to 5 requests per minute per user.',
        tags: ['AI'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/GenerateFlashcardsRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Flashcards generated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/GenerateFlashcardsResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input or rate limit exceeded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'code', type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'code', type: 'integer'),
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'code', type: 'integer'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(
        #[MapRequestPayload] GenerateFlashcardsRequest $request
    ): JsonResponse {
        $response = $this->flashcardsGenerator->generate($request);

        return $this->json($response);
    }
} 