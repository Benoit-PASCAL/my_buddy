<?php

namespace App\Tests\Chore\Controller;

use App\Chore\Entity\Assignment;
use App\Chore\Entity\Permission;
use App\Chore\Entity\Status;
use App\Chore\Entity\User;
use App\Chore\Repository\AssignmentRepository;
use App\Chore\Repository\PermissionRepository;
use App\Chore\Repository\StatusRepository;
use App\Chore\Repository\UserRepository;
use App\Tests\Helpers\DatabaseHelper;
use App\Tests\Helpers\SessionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    use SessionHelper;
    private KernelBrowser $client;
    private AssignmentRepository $assignmentRepository;
    private PermissionRepository $permissionRepository;
    private StatusRepository $statusRepository;
    private UserRepository $userRepository;
    private User $adminUser;
    private User $lambdaUser;
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
        $this->assignmentRepository = static::getContainer()->get('doctrine')->getRepository(Assignment::class);
        $this->permissionRepository = static::getContainer()->get('doctrine')->getRepository(Permission::class);
        $this->statusRepository = static::getContainer()->get('doctrine')->getRepository(Status::class);

        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $this->adminUser = $this->userRepository->findOneBy(['email' => 'admin@buddy.com']) ??
            (new User())
                ->setEmail('admin@buddy.com')
                ->setPassword('password');

        $this->lambdaUser = $this->userRepository->findOneBy(['email' => 'lambda@buddy.com']) ??
            (new User())
                ->setEmail('lambda@buddy.com')
                ->setPassword('password');

        foreach ($this->userRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testUserRegistrationWithValidDataCreatesUser(): void
    {
        DatabaseHelper::cleanDatabase();

        // Register first user

        $crawler = $this->client->request('POST', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $this->adminUser->getEmail(),
            'registration_form[plainPassword]' => $this->adminUser->getPassword(),
            'registration_form[confirmPassword]' => $this->adminUser->getPassword(),
        ]);

        $form['_token'] = $this->setCorrectToken('save');
        $this->client->submit($form);

        // Should redirect to the homepage
        $this->assertResponseRedirects('/');
        // User should be in the database
        $this->assertNotEmpty($this->userRepository->findOneBy(['email' => $this->adminUser->getEmail()]));
        // User should be admin
        $this->assertContains('ROLE_ADMIN', $this->userRepository->findOneBy(['email' => $this->adminUser->getEmail()])->getRoles());
        // User password should be hashed
        $this->assertNotEquals($this->adminUser->getPassword(), $this->userRepository->findOneBy(['email' => $this->adminUser->getEmail()])->getPassword());

        // Register second user

        $crawler = $this->client->request('POST', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $this->lambdaUser->getEmail(),
            'registration_form[plainPassword]' => $this->lambdaUser->getPassword(),
            'registration_form[confirmPassword]' => $this->lambdaUser->getPassword(),
        ]);

        $this->client->getContainer()->get('request_stack')->push($this->client->getRequest());
        $form['_token'] = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('save');

        $this->client->submit($form);

        // User should not be admin
        $this->assertNotContains('ROLE_ADMIN', $this->userRepository->findOneBy(['email' => $this->lambdaUser->getEmail()])->getRoles());


        DatabaseHelper::cleanDatabase();
    }

    public function testUserRegistrationWithoutTokenReturnsForm(): void
    {
        $session = $this->createSession($this->client);
        $crawler = $this->client->request('POST', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $this->adminUser->getEmail(),
            'registration_form[plainPassword]' => $this->adminUser->getPassword(),
            'registration_form[confirmPassword]' => $this->adminUser->getPassword(),
        ]);
        $form['_token'] = 'invalidToken';

        $this->client->submit($form);

        $this->assertResponseIsSuccessful(); // Indicate that the form was not submitted successfully
        $this->assertNull($this->userRepository->findOneBy(['email' => $this->lambdaUser->getEmail()]));

        DatabaseHelper::cleanDatabase();
    }

    public function testUserRegistrationWithInvalidDataReturnsForm(): void
    {
        $session = $this->createSession($this->client);
        $crawler = $this->client->request('POST', '/register');

        $validForm = [
            'registration_form[email]' => $this->lambdaUser->getEmail(),
            'registration_form[plainPassword]' => $this->lambdaUser->getPassword(),
            'registration_form[confirmPassword]' => $this->lambdaUser->getPassword(),
        ];

        // Test invalid data on each field
        foreach ($validForm as $key => $value) {
            foreach ($this->invalidData as $data) {
                $invalidForm = $validForm;
                $invalidForm[$key] = $data;

                $form = $crawler->selectButton('Register')->form($invalidForm);
                $form['_token'] = $this->setCorrectToken('save');
                $this->client->submit($form);

                // should not redirect to the homepage
                $this->assertResponseIsSuccessful(); // Indicate that the form was not submitted successfully
                // User should not be in the database
                $this->assertNull($this->userRepository->findOneBy(['email' => $this->lambdaUser->getEmail()]));
            }
        }

        // Test short password
        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $this->lambdaUser->getEmail(),
            'registration_form[plainPassword]' => 'pass',
            'registration_form[confirmPassword]' => 'pass',
        ]);
        $form['_token'] = $this->setCorrectToken('save');
        $this->client->submit($form);

        // should not redirect to the homepage
        $this->assertResponseIsSuccessful(); // Indicate that the form was not submitted successfully
        // User should not be in the database
        $this->assertNull($this->userRepository->findOneBy(['email' => $this->lambdaUser->getEmail()]));

        // Test mismatched passwords

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $this->lambdaUser->getEmail(),
            'registration_form[plainPassword]' => 'password',
            'registration_form[confirmPassword]' => 'password1',
        ]);
        $form['_token'] = $this->setCorrectToken('save');
        $this->client->submit($form);

        $this->assertResponseIsSuccessful(); // Indicate that the form was not submitted successfully
        $this->assertNull($this->userRepository->findOneBy(['email' => $this->lambdaUser->getEmail()]));

        DatabaseHelper::cleanDatabase();
    }

    public function testUserLoginWithValidDataLogsUser(): void
    {
        DatabaseHelper::cleanDatabase();
        $this->registerUser($this->adminUser);
        $this->logUserIn($this->adminUser);

        $this->assertResponseRedirects('http://localhost/');

        $this->client->request('GET', 'http://localhost/dashboard/my-profile');
        $this->assertResponseRedirects('http://localhost/dashboard/my-profile/');
    }

    public function testUserLoginWithoutTokenDataReturnsForm(): void
    {
        DatabaseHelper::cleanDatabase();
        $this->registerUser($this->adminUser);

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            '_username' => $this->adminUser->getEmail(),
            '_password' => $this->adminUser->getPassword(),
        ]);

        $this->client->getContainer()->get('request_stack')->push($this->client->getRequest());
        $form['_csrf_token'] = 'invalidToken';

        $this->client->submit($form);

        $this->assertResponseRedirects('http://localhost/login');
    }

    public function testUserLoginWithInvalidDataReturnsForm(): void
    {
        DatabaseHelper::cleanDatabase();
        $this->registerUser($this->adminUser);

        $validForm = [
            '_username' => $this->adminUser->getEmail(),
            '_password' => $this->adminUser->getPassword(),
        ];

        foreach ($validForm as $key => $value) {
            foreach ($this->invalidData as $data) {
                $invalidForm = $validForm;
                $invalidForm[$key] = $data;

                $crawler = $this->client->request('GET', '/login');

                $form = $crawler->selectButton('Login')->form($invalidForm);
                $form['_csrf_token'] = $this->setCorrectToken('authenticate');
                $this->client->submit($form);

                $this->assertResponseRedirects('http://localhost/login');
            }
        }
    }

    public function testUserLoginWithoutDatabaseReturnsForm()
    {
        DatabaseHelper::cleanDatabase();

        $crawler = $this->client->request('GET', '/login');

        $form = $form = $crawler->selectButton('Login')->form([
            '_username' => $this->adminUser->getEmail(),
            '_password' => $this->adminUser->getPassword(),
        ]);

        $this->client->getContainer()->get('request_stack')->push($this->client->getRequest());
        $form['_csrf_token'] = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $this->client->submit($form);

        $this->assertResponseRedirects('http://localhost/login');
    }

    public function testUserLogoutLogsUserOut(): void
    {
        DatabaseHelper::cleanDatabase();
        $this->registerUser($this->adminUser);
        $this->logUserIn($this->adminUser);

        $this->logUserOut();
        $this->assertResponseRedirects('http://localhost/');

        $this->client->request('GET', '/dashboard/my-profile/');
        $this->assertResponseRedirects('http://localhost/login');

        DatabaseHelper::cleanDatabase();
    }

    public function registerUser(User $user): void
    {
        $session = $this->createSession($this->client);
        $crawler = $this->client->request('POST', '/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $user->getEmail(),
            'registration_form[plainPassword]' => $user->getPassword(),
            'registration_form[confirmPassword]' => $user->getPassword(),
        ]);

        $this->client->getContainer()->get('request_stack')->push($this->client->getRequest());
        $form['_token'] = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('save');

        $this->client->submit($form);

        // Allow to test user login knowing that if registration logs user in,
        // test will still consider that user is logged out
        $this->logUserOut();
    }

    public function logUserIn(User $user): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            '_username' => $user->getEmail(),
            '_password' => $user->getPassword(),
        ]);

        $this->client->getContainer()->get('request_stack')->push($this->client->getRequest());
        //$form['_csrf_token'] = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');

        $this->client->submit($form);
    }

    public function logUserOut(): void
    {
        $this->client->request('GET', '/logout');
    }

    private function setCorrectToken(string $tokenId): string
    {
        $this->client->getContainer()->get('request_stack')->push($this->client->getRequest());
        return $this->client->getContainer()->get('security.csrf.token_manager')->getToken($tokenId);
    }
}