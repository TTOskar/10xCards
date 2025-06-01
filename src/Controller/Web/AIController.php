<?php

namespace App\Controller\Web;

use App\DTO\Request\AI\BulkSaveFlashcardsDTO;
use App\DTO\Request\AI\UpdateFlashcardDTO;
use App\Enum\AI\BulkAction;
use App\Enum\AI\FlashcardStatus;
use App\Form\AIGenerationType;
use App\Service\AI\FlashcardServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ai')]
#[IsGranted('ROLE_USER')]
class AIController extends AbstractController
{
    public function __construct(
        private readonly FlashcardServiceInterface $flashcardService
    ) {
    }

    #[Route('/flashcards', name: 'ai_flashcards_generate', methods: ['GET', 'POST'])]
    public function generateAction(Request $request): Response
    {
        $form = $this->createForm(AIGenerationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $result = $this->flashcardService->generateFromText($data['input_text']);

                return $this->redirectToRoute('ai_job_view', ['jobId' => $result['jobId']]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('ai/generate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/jobs/{jobId}', name: 'ai_job_view', methods: ['GET'])]
    public function viewJobAction(int $jobId): Response
    {
        try {
            $response = $this->flashcardService->getJobFlashcards($jobId);

            return $this->render('ai/job_view.html.twig', [
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('ai_flashcards_generate');
        }
    }

    #[Route('/flashcards/{flashcardId}/edit', name: 'ai_flashcard_edit', methods: ['PATCH'])]
    public function editFlashcardAction(
        Request $request,
        int $flashcardId
    ): Response {
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
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/jobs/{jobId}/bulk-save', name: 'ai_flashcards_bulk_save', methods: ['POST'])]
    public function bulkSaveAction(
        Request $request,
        int $jobId
    ): Response {
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

            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
} 