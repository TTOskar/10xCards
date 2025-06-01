namespace App\Tests\Service;

use App\Service\AIFlashcardGeneratorService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class AIFlashcardGeneratorServiceTest extends TestCase
{
    private AIFlashcardGeneratorService $service;
    private MockHttpClient $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = new MockHttpClient();
        $this->service = new AIFlashcardGeneratorService($this->httpClient);
    }

    public function testGenerateFromTextSuccess(): void
    {
        $responseData = [
            'jobId' => '123',
            'status' => 'completed',
            'flashcards' => [
                [
                    'id' => '1',
                    'front' => 'Test Front',
                    'back' => 'Test Back',
                    'status' => 'pending'
                ]
            ]
        ];

        $this->httpClient->setResponseFactory([
            new MockResponse(json_encode($responseData), ['http_code' => 200])
        ]);

        $result = $this->service->generateFromText('Test input text');

        $this->assertEquals('123', $result->jobId);
        $this->assertEquals('completed', $result->status);
        $this->assertCount(1, $result->flashcards);
        $this->assertEquals('Test Front', $result->flashcards[0]->front);
    }

    public function testGenerateFromTextRateLimitExceeded(): void
    {
        $this->httpClient->setResponseFactory([
            new MockResponse(json_encode(['error' => 'Rate limit exceeded']), [
                'http_code' => 429
            ])
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->service->generateFromText('Test input text');
    }
} 