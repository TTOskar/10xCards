<?php

namespace App\Controller\Api;

use App\DTO\Request\AI\BulkSaveFlashcardsDTO;
use App\DTO\Request\AI\UpdateFlashcardDTO;
use App\Enum\AI\BulkAction;
use App\Enum\AI\FlashcardStatus;
use App\Service\AI\FlashcardServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/ai')]
#[IsGranted('ROLE_USER')]
#[OA\Tag(name: 'AI Flashcards')]
class AIFlashcardController extends AbstractController
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService
    ) {
    }

    #[Route('/flashcards/{flashcardId}', name: 'api_flashcard_update', methods: ['PATCH'])]
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
        Request $request
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['status'])) {
                throw new \InvalidArgumentException('Status is required');
            }

            $dto = new UpdateFlashcardDTO(
                status: FlashcardStatus::from($data['status']),
                editedFront: $data['edited_front'] ?? null,
                editedBack: $data['edited_back'] ?? null
            );

            $response = $this->flashcardService->updateFlashcard($flashcardId, $dto);

            return $this->json($response);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/jobs/{jobId}/bulk-save', name: 'api_flashcards_bulk_save', methods: ['POST'])]
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
    public function bulkSave(
        int $jobId,
        Request $request
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['action'])) {
                throw new \InvalidArgumentException('Action is required');
            }

            $dto = new BulkSaveFlashcardsDTO(
                action: BulkAction::from($data['action']),
                deckId: $data['deck_id'] ?? null
            );

            $this->flashcardService->bulkSaveFlashcards($jobId, $dto);

            return $this->json([
                'message' => 'Flashcards processed successfully',
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 