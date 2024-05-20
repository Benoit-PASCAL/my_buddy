<?php

namespace App\Tests\App\Controller;

use App\App\Entity\Event;
use App\App\Repository\EventRepository;
use App\Chore\Entity\User;
use App\Chore\Repository\UserRepository;
use App\Tests\Helpers\DatabaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EventRepository $eventRepository;
    private UserRepository $userRepository;
    private string $path = '/dashboard/event/';
    private array $sampleEventArray = [
        'title' => 'Sample Event',
        'description' => 'This is a sample event.',
        'startDate' => '2022-01-01 00:00:00',
        'endDate' => '2022-01-01 23:59:59',
    ];
    private Event $sampleEvent;
    private User $adminUser;
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
        $this->eventRepository = static::getContainer()->get('doctrine')->getRepository(Event::class);
        DatabaseHelper::loadFixtures();

        $this->adminUser = $this->userRepository->findOneBy(['email' => 'admin@buddy.com']);

        $this->sampleEvent = (new Event())
            ->setTitle($this->sampleEventArray['title'])
            ->setDescription($this->sampleEventArray['description'])
            ->setStartDate(new \DateTime($this->sampleEventArray['startDate']))
            ->setEndDate(new \DateTime($this->sampleEventArray['endDate']));

        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $this->manager->clear();
        //foreach ($this->eventRepository->findAll() as $object) {
        //    $this->manager->remove($object);
        //}
    }

    public function testEventIndexRendersCorrectly(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event index');
    }

    public function testNewEventFormSubmissionCreatesEvent(): void
    {
        $originalNumObjectsInRepository = count($this->eventRepository->findAll());

        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'event[title]' => $this->sampleEventArray['title'],
            'event[description]' => $this->sampleEventArray['description'],
            'event[startDate]' => $this->sampleEventArray['startDate'],
            'event[endDate]' => $this->sampleEventArray['endDate'],
        ]);

        self::assertResponseRedirects('/dashboard/event/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->eventRepository->findAll()));

    }

    public function testEventShowRendersCorrectly(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $this->manager->persist($this->sampleEvent);
        $this->manager->flush();

        $event = $this->eventRepository->findOneBy(['title' => $this->sampleEventArray['title']]);

        $this->client->request('GET', sprintf('%s%s', $this->path, $event->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event');

        // Check if the event's details are displayed
        self::assertSelectorTextContains('body', $this->sampleEventArray['title']);
    }

    public function testEventEditFormSubmissionUpdatesEvent(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $this->manager->persist($this->sampleEvent);
        $this->manager->flush();

        $event = $this->eventRepository->findOneBy(['title' => $this->sampleEventArray['title']]);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $event->getId()));

        $this->client->submitForm('Update', [
            'event[title]' => 'New Title',
            'event[description]' => 'New Description',
            'event[startDate]' => '2024-01-01 00:00:00',
            'event[endDate]' => '2024-01-01 23:59:59',
        ]);

        self::assertResponseRedirects('/dashboard/event/');

        $fixture = $this->eventRepository->findOneBy(['id' => $event->getId()]);
        $this->manager->refresh($fixture);

        self::assertSame('New Title', $fixture->getTitle());
        self::assertSame('New Description', $fixture->getDescription());
        self::assertSame('2024-01-01 00:00:00', $fixture->getStartDate()->format('Y-m-d H:i:s'));
        self::assertSame('2024-01-01 23:59:59', $fixture->getEndDate()->format('Y-m-d H:i:s'));
    }

    public function testEventDeleteRemovesEvent(): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Login', [
            '_username' => $this->adminUser->getEmail(),
            '_password' => 'password',
        ]);

        $this->manager->persist($this->sampleEvent);
        $this->manager->flush();

        $originalNumObjectsInRepository = count($this->eventRepository->findAll());

        $event = $this->eventRepository->findOneBy(['title' => $this->sampleEventArray['title']]);

        $this->client->request('GET', sprintf('%s%s', $this->path, $event->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository - 1, count($this->eventRepository->findAll()));
        self::assertResponseRedirects('/dashboard/event/');
    }
}
