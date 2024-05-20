<?php

namespace App\Tests\Helpers;

use App\App\Entity\Event;
use App\Chore\Entity\Assignment;
use App\Chore\Entity\Permission;
use App\Chore\Entity\Status;
use App\Chore\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DatabaseHelper extends WebTestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    public static function cleanDatabase(): void
    {
        $permissionRepo = static::getContainer()->get('doctrine')->getRepository(Permission::class);
        $permissionRepo->createQueryBuilder('p')
            ->delete()
            ->getQuery()
            ->execute();

        $assignmentRepo = static::getContainer()->get('doctrine')->getRepository(Assignment::class);
        $assignmentRepo->createQueryBuilder('a')
            ->delete()
            ->getQuery()
            ->execute();

        $userRepo = static::getContainer()->get('doctrine')->getRepository(User::class);
        $userRepo->createQueryBuilder('u')
            ->delete()
            ->getQuery()
            ->execute();

        $statusRepo = static::getContainer()->get('doctrine')->getRepository(Status::class);
        $statusRepo->createQueryBuilder('s')
            ->delete()
            ->getQuery()
            ->execute();

        $statusRepo = static::getContainer()->get('doctrine')->getRepository(Event::class);
        $statusRepo->createQueryBuilder('e')
            ->delete()
            ->getQuery()
            ->execute();
    }

    public static function loadFixtures(): void
    {
        self::cleanDatabase();
        $manager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $adminRole = new Status();
        $adminRole
            ->setType(Status::ROLE_TYPE)
            ->setLabel('admin')
            ->setIcon('bi-person-fill');

        $roles = [];
        $manager->persist($adminRole);

        foreach (['editable', 'deletable'] as $roleName) {
            $role = new Status();
            $role
                ->setType(Status::ROLE_TYPE)
                ->setLabel($roleName);

            $roles[] = $role;

            $manager->persist($role);
        }

        foreach (Permission::getAppControllersList() as $controllerName) {
            $controller = new Status();

            $controller
                ->setType(Status::CONTROLLER_TYPE)
                ->setLabel($controllerName);

            $manager->persist($controller);

            $permission = new Permission();
            $permission
                ->setController($controller)
                ->setRole($adminRole)
                ->setAccess(Permission::CAN_ALL);

            $manager->persist($permission);

            foreach ($roles as $role) {
                $permission = new Permission();
                $permission
                    ->setController($controller)
                    ->setRole($role)
                    ->setAccess(Permission::CAN_ALL);

                $manager->persist($permission);
            }

        }

        $adminAssignment = new Assignment();
        $adminAssignment
            ->setRole($adminRole)
            ->setStartDate(new \DateTimeImmutable());

        $adminUser = new User();
        $adminUser
            ->setEmail('admin@buddy.com')
            ->setPassword($passwordHasher->hashPassword($adminUser, 'password'))
            ->setSecretKey($passwordHasher->hashPassword($adminUser, rand(100000, 999999)))
            ->addAssignment($adminAssignment);
        $manager->persist($adminUser);

        $lambdaUser = new User();
        $lambdaUser
            ->setEmail('lambda@buddy.com')
            ->setPassword($passwordHasher->hashPassword($adminUser, 'password'))
            ->setSecretKey($passwordHasher->hashPassword($adminUser, rand(100000, 999999)));
        $manager->persist($lambdaUser);

        $modifiableUser = new User();
        $modifiableUser
            ->setEmail('editable@buddy.com')
            ->setPassword($passwordHasher->hashPassword($adminUser, 'password'))
            ->setSecretKey($passwordHasher->hashPassword($adminUser, rand(100000, 999999)));
        $manager->persist($modifiableUser);

        $deletableUser = new User();
        $deletableUser
            ->setEmail('deletable@buddy.com')
            ->setPassword($passwordHasher->hashPassword($adminUser, 'password'))
            ->setSecretKey($passwordHasher->hashPassword($adminUser, rand(100000, 999999)));
        $manager->persist($deletableUser);

        $manager->flush();
    }
}