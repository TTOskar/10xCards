<?php

declare(strict_types=1);

namespace App\Service\AI\Client;

use App\DTO\Response\AI\FlashcardDTO;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAiClient implements AiClientInterface
{
    private int $lastRequestTokenCount = 0;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4-turbo-preview',
    ) {
    }

    /**
     * @return FlashcardDTO[]
     */
    public function generateFlashcards(string $inputText): array
    {
        // TODO: Implement real OpenAI API call
        // For now, return mock data
        $this->lastRequestTokenCount = random_int(100, 500);

        return [
            new FlashcardDTO(
                'What is the capital of France?',
                'Paris is the capital of France.'
            ),
            new FlashcardDTO(
                'Who wrote "Romeo and Juliet"?',
                'William Shakespeare wrote "Romeo and Juliet".'
            ),
            new FlashcardDTO(
                'What is the chemical symbol for gold?',
                'Au (Aurum) is the chemical symbol for gold.'
            ),
        ];
    }

    public function getLastRequestTokenCount(): int
    {
        return $this->lastRequestTokenCount;
    }

    private function callOpenAiApi(string $inputText): array
    {
        // This is the structure for the future implementation
        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that creates flashcards from provided text. Each flashcard should have a question on the front and an answer on the back.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $inputText,
                    ],
                ],
                'temperature' => 0.7,
            ],
        ]);

        return $response->toArray();
    }
} 