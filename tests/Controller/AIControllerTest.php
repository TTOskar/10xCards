namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AIControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testGenerateActionGet(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/ai/flashcards');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="ai_generation"]');
        $this->assertSelectorExists('textarea[name="ai_generation[input_text]"]');
    }

    public function testGenerateActionPostSuccess(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('POST', '/ai/generate', [
            'ai_generation' => [
                'input_text' => str_repeat('a', 1000), // Valid input length
                '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ai_generation')->getValue()
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGenerateActionPostInvalidInput(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('POST', '/ai/generate', [
            'ai_generation' => [
                'input_text' => 'too short', // Invalid input length
                '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ai_generation')->getValue()
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
} 