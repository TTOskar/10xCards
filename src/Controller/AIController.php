<?php

namespace App\Controller;

use App\Form\AIGenerationType;
use App\Form\FlashcardEditType;
use App\Service\AIFlashcardGeneratorService;
use App\Service\UserLimitsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;

class AIController extends AbstractController
{
    private AIFlashcardGeneratorService $generatorService;
    private UserLimitsService $limitsService;

    public function __construct(
        AIFlashcardGeneratorService $generatorService,
        UserLimitsService $limitsService
    ) {
        $this->generatorService = $generatorService;
        $this->limitsService = $limitsService;
    }

    #[Route('/ai/flashcards', name: 'ai_flashcards_generate', methods: ['GET', 'POST'])]
    public function generate(Request $request): Response
    {
        $form = $this->createForm(AIGenerationType::class);
        $form->handleRequest($request);

        $userLimits = $this->limitsService->getUserLimits($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $this->generatorService->generateFromText(
                    $form->get('input_text')->getData()
                );

                $this->addFlash('success', 'Fiszki zostały wygenerowane pomyślnie.');

                return $this->render('ai/generate.html.twig', [
                    'form' => $form->createView(),
                    'user_limits' => $userLimits,
                    'generated_flashcards' => $result->flashcards,
                ]);

            } catch (ClientException $e) {
                if ($e->getResponse()->getStatusCode() === 429) {
                    $this->addFlash('error', 'Przekroczono limit zapytań. Spróbuj ponownie za chwilę.');
                } else {
                    $this->addFlash('error', 'Nieprawidłowe dane wejściowe. Sprawdź wprowadzony tekst.');
                }
            } catch (ServerException $e) {
                $this->addFlash('error', 'Wystąpił błąd serwera. Spróbuj ponownie później.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie później.');
            }
        }

        return $this->render('ai/generate.html.twig', [
            'form' => $form->createView(),
            'user_limits' => $userLimits,
        ]);
    }

    #[Route('/ai/flashcards/{flashcardId}/edit', name: 'ai_flashcard_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $flashcardId): Response
    {
        $flashcard = $this->generatorService->getFlashcard($flashcardId);
        
        if (!$flashcard) {
            throw $this->createNotFoundException('Fiszka nie została znaleziona.');
        }

        $form = $this->createForm(FlashcardEditType::class, $flashcard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->generatorService->updateFlashcard($flashcardId, $form->getData());
                $this->addFlash('success', 'Fiszka została zaktualizowana.');

                return $this->redirectToRoute('ai_flashcards_generate');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Nie udało się zaktualizować fiszki. Spróbuj ponownie.');
            }
        }

        return $this->render('ai/edit_flashcard.html.twig', [
            'form' => $form->createView(),
            'flashcard' => $flashcard,
        ]);
    }
} 