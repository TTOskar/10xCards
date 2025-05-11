<?php

declare(strict_types=1);

namespace App\Controller\Api\AI;

use App\DTO\Request\AI\BulkSaveFlashcardsDTO;
use App\DTO\Request\AI\UpdateFlashcardDTO;
use App\Enum\AI\BulkAction;
use App\Enum\AI\FlashcardStatus;
use App\Service\AI\FlashcardServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/ai')]
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: 'AI Flashcards')]
final class FlashcardController extends AbstractController
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService,
    ) {
    }

    #[Route('/jobs/{jobId}/flashcards', methods: ['GET'])]
    #[OA\Get(
        path: '/api/ai/jobs/{jobId}/flashcards',
        summary: 'Get flashcards for a specific AI job',
        description: 'Retrieves a paginated list of flashcards generated for the specified AI job.'
    )]
    #[OA\Parameter(
        name: 'jobId',
        description: 'AI Job ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number (1-based)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Number of items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 20, minimum: 1, maximum: 100)
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns a list of flashcards for the specified AI job',
        content: new OA\JsonContent(ref: '#/components/schemas/AIJobFlashcardsResponse')
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - user not logged in',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - user does not have access to this job',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 404,
        description: 'AI Job not found',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    public function getJobFlashcards(
        int $jobId,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter(name: 'per_page')] int $perPage = 20,
    ): JsonResponse {
        $response = $this->flashcardService->getJobFlashcards($jobId, $page, $perPage);

        return $this->json($response);
    }

    #[Route('/flashcards/{flashcardId}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/ai/flashcards/{flashcardId}',
        summary: 'Update a flashcard',
        description: 'Updates a flashcard\'s status and optionally its content.'
    )]
    #[OA\Parameter(
        name: 'flashcardId',
        description: 'Flashcard ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(
                    property: 'status',
                    type: 'string',
                    enum: ['accepted', 'rejected'],
                    description: 'New status for the flashcard'
                ),
                new OA\Property(
                    property: 'edited_front',
                    type: 'string',
                    nullable: true,
                    maxLength: 200,
                    description: 'Edited front content'
                ),
                new OA\Property(
                    property: 'edited_back',
                    type: 'string',
                    nullable: true,
                    maxLength: 1000,
                    description: 'Edited back content'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Flashcard updated successfully',
        content: new OA\JsonContent(ref: '#/components/schemas/FlashcardResponse')
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - user not logged in',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - user does not have access to this flashcard',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 404,
        description: 'Flashcard not found',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid status transition or validation error',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    public function updateFlashcard(
        int $flashcardId,
        #[MapRequestPayload] UpdateFlashcardDTO $dto,
    ): JsonResponse {
        $response = $this->flashcardService->updateFlashcard($flashcardId, $dto);

        return $this->json($response);
    }

    #[Route('/jobs/{jobId}/bulk-save', methods: ['POST'])]
    #[OA\Post(
        path: '/api/ai/jobs/{jobId}/bulk-save',
        summary: 'Bulk save or reject flashcards',
        description: 'Performs a bulk operation (save/reject) on all pending flashcards for a specific job.'
    )]
    #[OA\Parameter(
        name: 'jobId',
        description: 'AI Job ID',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['action'],
            properties: [
                new OA\Property(
                    property: 'action',
                    type: 'string',
                    enum: ['save', 'reject'],
                    description: 'Action to perform on pending flashcards'
                ),
                new OA\Property(
                    property: 'deck_id',
                    type: 'integer',
                    nullable: true,
                    description: 'Deck ID (required when action is "save")'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Operation completed successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Flashcards processed successfully'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Unauthorized - user not logged in',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 403,
        description: 'Forbidden - user does not have access to this job or deck',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 404,
        description: 'AI Job or Deck not found',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid request or no flashcards to process',
        content: new OA\JsonContent(ref: '#/components/schemas/Error')
    )]
    public function bulkSaveFlashcards(
        int $jobId,
        #[MapRequestPayload] BulkSaveFlashcardsDTO $dto,
    ): JsonResponse {
        $this->flashcardService->bulkSaveFlashcards($jobId, $dto);

        return $this->json([
            'message' => 'Flashcards processed successfully',
        ]);
    }
} 