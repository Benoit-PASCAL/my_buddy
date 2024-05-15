<?php

namespace App\Chore\Controller;

use App\Chore\Entity\Permission;
use App\Chore\Entity\User;
use App\Chore\Form\UserType;
use App\Chore\Repository\UserRepository;
use App\Chore\Service\RequestAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * UserController is a controller that handles user related actions.
 * It extends the RightsController to check for permissions.
 *
 */
#[Route('/dashboard/user')]
class UserController extends RightsController
{
    private SluggerInterface $slugger;

    /**
     * Constructor for UserController.
     *
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Display the list of users.
     *
     * @param UserRepository $userRepository
     * @return Response
     */
    #[Route('/', name: 'app_user_index', methods: ['GET', 'POST'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        $sort = RequestAnalyzer::getSortParams($request, new User());
        $users = $userRepository->findBy([], $sort);

        return $this->render('chore/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Create a new user.
     * When a new user is created, a random secret key is generated for the user. This key is used for password reset.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return Response
     */
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $this->checkRights(Permission::CAN_CREATE);

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword('')
                ->setProfilePicture('')
                ->setSecretKey($userPasswordHasher->hashPassword($user, rand(100000, 999999)));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Display a specific user.
     *
     * @param User $user
     * @return Response
     */
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        return $this->render('chore/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit a specific user.
     *
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_EDIT);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $picture */
            $picture = $form->get('profilePicture')->getData();
            if ($picture) {
                $newPathName = $this->setUniquePath($picture);
                $user->setProfilePicture($newPathName);
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Delete a specific user.
     *
     * @param Request $request
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_DELETE);

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Set a unique path for the uploaded profile picture.
     *
     * @param UploadedFile $picture
     * @return string The new path name.
     */
    public function setUniquePath(UploadedFile $picture): string
    {
        $originalName = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
        $newName = $this->slugger->slug($originalName).'-'.uniqid().'.'.$picture->guessExtension();

        try {
            $picture->move(
                'uploads/profile_pictures',
                $newName
            );
        } catch (FileException $e) {

        }

        return $newName;
    }
}
