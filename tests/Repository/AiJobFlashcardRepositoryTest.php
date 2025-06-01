namespace App\Tests\Repository;

use App\Entity\AI\AiJob;
use App\Entity\AI\AiJobFlashcard;
use App\Entity\User;
use App\Repository\AiJobFlashcardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AiJobFlashcardRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private AiJobFlashcardRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = static::getContainer()->get(AiJobFlashcardRepository::class);
    }

    public function testCountTodayFlashcardsForUser(): void
    {
        $user = new User();
        $job = new AiJob();
        $job->setUser($user);
        
        $flashcard1 = new AiJobFlashcard();
        $flashcard1->setJob($job);
        $flashcard1->setCreatedAt(new \DateTimeImmutable());
        
        $flashcard2 = new AiJobFlashcard();
        $flashcard2->setJob($job);
        $flashcard2->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->persist($job);
        $this->entityManager->persist($flashcard1);
        $this->entityManager->persist($flashcard2);
        $this->entityManager->flush();

        $count = $this->repository->countTodayFlashcardsForUser($user);
        $this->assertEquals(2, $count);
    }

    public function testFindPaginatedByJob(): void
    {
        $user = new User();
        $job = new AiJob();
        $job->setUser($user);

        for ($i = 0; $i < 5; $i++) {
            $flashcard = new AiJobFlashcard();
            $flashcard->setJob($job);
            $flashcard->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($flashcard);
        }

        $this->entityManager->persist($user);
        $this->entityManager->persist($job);
        $this->entityManager->flush();

        $result = $this->repository->findPaginatedByJob($job, 1, 3);
        $this->assertCount(3, $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
} 