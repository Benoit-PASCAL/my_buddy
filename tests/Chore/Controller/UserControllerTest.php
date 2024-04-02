<?php

namespace App\Tests\Chore\Controller;

use App\Chore\Entity\User;
use App\Chore\Repository\UserRepository;
use App\Tests\Helpers\DatabaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private User $adminUser;
    private User $lambdaUser;
    private string $path = '/dashboard/user/';
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

    public function testUserIndexRendersCorrectly(): void
    {
        $this->client->loginUser($this->adminUser);
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');
    }

    public function testNewUserFormSubmissionCreatesUser(): void
    {
        $originalNumObjectsInRepository = count($this->userRepository->findAll());

        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[email]' => 'newuser@buddy.com',
            'user[lastName]' => '',
            'user[firstName]' => '',
            'user[phone]' => '',
            'user[profilePicture]' => '',
        ]);

        self::assertResponseRedirects('/dashboard/user/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->userRepository->findAll()));
    }

    public function testUserShowRendersCorrectly(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $user = $this->userRepository->findOneBy(['email' => 'lambda@buddy.com']);

        $this->client->request('GET', sprintf('%s%s', $this->path, $user->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Check if the user's details are displayed
        self::assertSelectorTextContains('body', 'lambda@buddy.com');
    }

    public function testUserEditFormSubmissionUpdatesUser(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $user = $this->userRepository->findOneBy(['email' => 'editable@buddy.com']);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $user->getId()));

        $this->client->submitForm('Update', [
            'user[email]' => 'editable_2@buddy.com',
            'user[lastName]' => 'Do',
            'user[firstName]' => 'Jane',
            'user[phone]' => '1234567890',
        ]);

        self::assertResponseRedirects('/dashboard/user/');

        $fixture = $this->userRepository->findOneBy(['id' => $user->getId()]);
        $this->manager->refresh($fixture);

        self::assertSame('editable_2@buddy.com', $fixture->getEmail());
        self::assertSame('Do', $fixture->getLastName());
        self::assertSame('Jane', $fixture->getFirstName());
        self::assertSame('1234567890', $fixture->getPhone());
    }

    public function testUserDeleteRemovesUser(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $originalNumObjectsInRepository = count($this->userRepository->findAll());

        $user = $this->userRepository->findOneBy(['email' => 'deletable@buddy.com']);

        $this->client->request('GET', sprintf('%s%s', $this->path, $user->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository - 1, count($this->userRepository->findAll()));
        self::assertResponseRedirects('/dashboard/user/');
    }
}
