<?php

namespace App\Chore\Controller;

use App\Chore\Entity\Assignment;
use App\Chore\Entity\Permission;
use App\Chore\Entity\Status;
use App\Chore\Entity\User;
use App\Chore\Form\PasswordSetterFormType;
use App\Chore\Form\RegistrationFormType;
use App\Chore\Repository\AssignmentRepository;
use App\Chore\Repository\PermissionRepository;
use App\Chore\Repository\StatusRepository;
use App\Chore\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    private AssignmentRepository $assignmentRepository;
    private PermissionRepository $permissionRepository;
    private StatusRepository $statusRepository;
    private UserRepository $userRepository;

    public function __construct(
        AssignmentRepository $assignmentRepository,
        PermissionRepository $permissionRepository,
        StatusRepository $statusRepository,
        UserRepository $userRepository
    )
    {
        $this->assignmentRepository = $assignmentRepository;
        $this->permissionRepository = $permissionRepository;
        $this->statusRepository = $statusRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if(!$this->isCsrfTokenValid('save', $request->request->get('_token'))) {
                $form->addError(new FormError('Invalid CSRF Token'));
                return $this->render('chore/registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if($form->get('plainPassword')->getData() !== $form->get('confirmPassword')->getData()) {
                $form->addError(new FormError('Password and Confirm Password do not match'));
                return $this->render('chore/registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ))
                ->setSecretKey($userPasswordHasher->hashPassword($user, rand(100000, 999999)))
            ;

            if($this->isFirstUser()) {
                $superAdminAssignment = $this->createSuperAdminAssignment($entityManager);
                $user->addAssignment($superAdminAssignment);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('chore/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/save', name: 'app_save', methods: ['GET', 'POST'])]
    public function save(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $registration_form = $request->request->getIterator('registration_form');
        if(count($registration_form) > 0) {
            $user_email = $registration_form['registration_form']['email'];
            $user = $this->userRepository->findOneBy(['email' => $user_email]);
        } else {
            $user = new User();
        }
        $form = $this->createForm(PasswordSetterFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            if(!$this->isCsrfTokenValid('save', $request->request->get('_token'))) {
                $form->addError(new FormError('Invalid CSRF Token'));
                return $this->render('chore/registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if($form->get('secretToken')->getData() !== $user->getSecretKey()) {
                $form->addError(new FormError('Invalid Secret Key'));
                return $this->render('chore/registration/password_setter.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if($form->get('plainPassword')->getData() !== $form->get('confirmPassword')->getData()) {
                $form->addError(new FormError('Password and Confirm Password do not match'));
                return $this->render('chore/registration/password_setter.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ))
            ;

            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chore/registration/password_setter.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('chore/registration/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    private function isFirstUser(): bool
    {
        return $this->userRepository->count() === 0;
    }

    /**
     * Initialize the database with the first user and give him all the permissions
     * @param $entityManager
     * @return void
     */
    private function createSuperAdminAssignment($entityManager): Assignment
    {
        $adminRole = $this->createSuperAdminRole();

        foreach (Permission::CONTROLLER_LIST as $controllerName) {
            $newController = $this->createController($controllerName);
            $entityManager->persist($newController);

            $permission = $this->createPermission($newController, $adminRole);
            $entityManager->persist($permission);
        }
        $entityManager->persist($adminRole);

        return $this->createAssignment($adminRole);
    }

    private function createSuperAdminRole(): Status
    {
        return $this->statusRepository->findOneBy([
            'label' => 'admin'
        ]) ?? (new Status())
            ->setType(Status::ROLE_TYPE)
            ->setLabel('admin')
            ->setIcon('bi-person-fill');
    }

    private function createController(string $controllerName): Status
    {
        return $this->statusRepository->findOneBy([
            'label' => $controllerName
        ]) ?? (new Status())
            ->setType(Status::CONTROLLER_TYPE)
            ->setLabel($controllerName);
    }

    private function createPermission(Status $controller, Status $role): Permission
    {
        return $this->permissionRepository->findOneBy([
            'controller' => $controller,
            'role' => $role
        ]) ?? (new Permission())
            ->setController($controller)
            ->setRole($role)
            ->setAccess(Permission::CAN_ALL);
    }

    private function createAssignment(Status $adminRole): Assignment
    {
        $assignment =  $this->assignmentRepository->findOneBy([
            'role' => $adminRole
        ]) ?? (new Assignment())
            ->setRole($adminRole);

        return $assignment
            ->setStartDate(new \DateTimeImmutable());
    }
}
