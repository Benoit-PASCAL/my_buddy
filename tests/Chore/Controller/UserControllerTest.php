<?php

namespace App\Tests\Chore\Controller;

use App\Chore\Entity\User;
use App\Chore\Repository\UserRepository;
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

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);

        $this->adminUser = $this->userRepository->findOneBy(['email' => 'admin@buddy.com']);
        $this->lambdaUser = $this->userRepository->findOneBy(['email' => 'lambda@buddy.com']);

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testUserIndexRendersCorrectly(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNewUserFormSubmissionCreatesUser(): void
    {
        $originalNumObjectsInRepository = count($this->userRepository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'user[email]' => 'Testing',
            'user[roles]' => 'Testing',
            'user[password]' => 'Testing',
            'user[lastName]' => 'Testing',
            'user[firstName]' => 'Testing',
            'user[phone]' => 'Testing',
            'user[profilePicture]' => 'Testing',
        ]);

        self::assertResponseRedirects('/user/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->userRepository->findAll()));
    }

    public function testUserShowRendersCorrectly(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles('My Title');
        $fixture->setPassword('My Title');
        $fixture->setLastName('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setPhone('My Title');
        $fixture->setProfilePicture('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testUserEditFormSubmissionUpdatesUser(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles('My Title');
        $fixture->setPassword('My Title');
        $fixture->setLastName('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setPhone('My Title');
        $fixture->setProfilePicture('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[email]' => 'Something New',
            'user[roles]' => 'Something New',
            'user[password]' => 'Something New',
            'user[lastName]' => 'Something New',
            'user[firstName]' => 'Something New',
            'user[phone]' => 'Something New',
            'user[profilePicture]' => 'Something New',
        ]);

        self::assertResponseRedirects('/user/');

        $fixture = $this->userRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getRoles());
        self::assertSame('Something New', $fixture[0]->getPassword());
        self::assertSame('Something New', $fixture[0]->getLastName());
        self::assertSame('Something New', $fixture[0]->getFirstName());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getProfilePicture());
    }

    public function testUserDeleteRemovesUser(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->userRepository->findAll());

        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles('My Title');
        $fixture->setPassword('My Title');
        $fixture->setLastName('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setPhone('My Title');
        $fixture->setProfilePicture('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->userRepository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->userRepository->findAll()));
        self::assertResponseRedirects('/user/');
    }
}
