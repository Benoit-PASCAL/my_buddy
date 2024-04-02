<?php

namespace App\Tests\Chore\Controller;

use App\Chore\Entity\Status;
use App\Chore\Entity\User;
use App\Chore\Repository\StatusRepository;
use App\Chore\Repository\UserRepository;
use App\Tests\Helpers\DatabaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private StatusRepository $roleRepository;
    private UserRepository $userRepository;
    private User $adminUser;
    private User $lambdaUser;
    private string $path = '/dashboard/role/';
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
        $this->roleRepository = static::getContainer()->get('doctrine')->getRepository(Status::class);
        DatabaseHelper::loadFixtures();

        $this->adminUser = $this->userRepository->findOneBy(['email' => 'admin@buddy.com']);

        $this->manager = static::getContainer()->get('doctrine')->getManager();

        foreach ($this->roleRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testUserIndexRendersCorrectly(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);
        $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Role index');
    }

    public function testNewUserFormSubmissionCreatesUser(): void
    {
        $originalNumObjectsInRepository = count($this->roleRepository->findAll());

        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'role[label]' => 'otherrole',
            'role[color]' => '000000',
            'role[icon]' => 'bi-person-fill',
            'role[permissions][0][access]' => '5',
            'role[permissions][1][access]' => '5',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->roleRepository->findAll()));
    }

    public function testUserShowRendersCorrectly(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $role = $this->roleRepository->findOneBy(['label' => 'admin']);

        $this->client->request('GET', sprintf('%s%s', $this->path, $role->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Role');

        // Check if the user's details are displayed
        self::assertSelectorTextContains('body', 'admin');
    }

    public function testUserEditFormSubmissionUpdatesUser(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $role = $this->roleRepository->findOneBy(['label' => 'editable']);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $role->getId()));

        $this->client->submitForm('Update', [
            'role[label]' => 'edited',
            'role[color]' => '000000',
            'role[icon]' => 'bi-person-fill',
            'role[permissions][0][access]' => '5',
            'role[permissions][1][access]' => '5',
        ]);

        self::assertResponseRedirects($this->path);

        $fixture = $this->roleRepository->findOneBy(['id' => $role->getId()]);
        $this->manager->refresh($fixture);

        self::assertSame('edited', $fixture->getLabel());
        self::assertSame('000000', $fixture->getColor());
        self::assertSame('bi-person-fill', $fixture->getIcon());
        self::assertSame(5, $fixture->getPermissions()[0]->getAccess());
        self::assertSame(5, $fixture->getPermissions()[1]->getAccess());
    }

    public function testUserDeleteRemovesUser(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $originalNumObjectsInRepository = count($this->roleRepository->findAll());

        $role = $this->roleRepository->findOneBy(['label' => 'deletable']);

        $this->client->request('GET', sprintf('%s%s', $this->path, $role->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository - 1, count($this->roleRepository->findAll()));
        self::assertResponseRedirects($this->path);
    }
}
