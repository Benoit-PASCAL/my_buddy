<?php

namespace App\Controller\Chore;

use App\Entity\Chore\User;
use App\Form\Chore\PasswordSetterFormType;
use App\Form\Chore\RegistrationFormType;
use App\Repository\Chore\UserRepository;
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
    private UserRepository $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ))
                ->setLastName('')
                ->setFirstName('')
                ->setPhone('')
                ->setProfilePicture('')
                ->setSecretKey($userPasswordHasher->hashPassword($user, rand(100000, 999999)))
            ;

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
}
