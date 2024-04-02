<?php

namespace App\Tests\Chore\Controller;

use App\Chore\Entity\User;
use App\Chore\Repository\UserRepository;
use App\Tests\Helpers\DatabaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private User $adminUser;
    private User $lambdaUser;
    private string $path = '/dashboard/my-profile/';
    private EntityManagerInterface $manager;
    private array $invalidData = [
        'invalidData', // Invalid data
        '', // Empty string
        null, // Empty data
        '\'OR 1=1; --', // SQL Injection
        '<script>alert("Hello")</script>', // XSS
    ];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        DatabaseHelper::loadFixtures();

        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->adminUser = $this->userRepository->findOneBy(['email' => 'admin@buddy.com']);
        $this->lambdaUser = $this->userRepository->findOneBy(['email' => 'lambda@buddy.com']);

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testUserShowRendersCorrectly(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $user = $this->userRepository->findOneBy(['email' => $this->adminUser->getEmail()]);

        $this->client->request('GET', sprintf('%s', $this->path));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Check if the user's details are displayed
        self::assertSelectorTextContains('body', $this->adminUser->getEmail());
    }

    public function testUserEditFormSubmissionUpdatesUser(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->lambdaUser->getEmail(),
            '_password' => 'password',
        ]);

        $user = $this->userRepository->findOneBy(['email' => $this->lambdaUser->getEmail()]);

        $this->client->request('GET', sprintf('%sedit', $this->path));

        $this->client->submitForm('Update', [
            'profile[email]' => 'edited@buddy.com',
            'profile[lastName]' => 'Do',
            'profile[firstName]' => 'Jane',
            'profile[phone]' => '1234567890',
        ]);

        self::assertResponseRedirects($this->path);

        $fixture = $this->userRepository->findOneBy(['id' => $user->getId()]);
        $this->manager->refresh($fixture);

        self::assertSame('edited@buddy.com', $fixture->getEmail());
        self::assertSame('Do', $fixture->getLastName());
        self::assertSame('Jane', $fixture->getFirstName());
        self::assertSame('1234567890', $fixture->getPhone());
    }
}
