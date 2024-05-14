<?php

namespace App\Chore\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * HomeController is a controller that handles home page related actions.
 * It extends the AbstractController to use Symfony's base controller functionalities.
 */
class HomeController extends AbstractController
{
    /**
     * Display the home page.
     *
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('chore/home/index.html.twig', [
            'title' => 'Hello',
        ]);
    }

    /**
     * Display the under construction page.
     *
     * @return Response
     */
    #[Route('/', name: 'app_under_construction')]
    public function default(): Response
    {
        return $this->render('chore/home/building.html.twig', [
            'title' => 'Website under construction' ,
            'message' => 'Please come back later.'
        ]);
    }
}
