<?php

namespace App\Chore\Controller;

use App\Chore\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * ProfileController is a controller that handles profile related actions.
 * It extends the RightsController to check for permissions.
 *
 */
#[Route('/dashboard/my-profile')]
class ProfileController extends RightsController
{
    private SluggerInterface $slugger;

    /**
     * Constructor for ProfileController.
     *
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * Display the current user's profile.
     *
     * @return Response
     */
    #[Route('/', name: 'app_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        $user = $this->getUser();

        return $this->render('chore/user/profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Edit the current user's profile.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $picture */
            $picture = $form->get('profilePicture')->getData();
            if ($picture) {
                $newPathName = $this->setUniquePath($picture);
                $user->setProfilePicture($newPathName);
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/user/profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
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
