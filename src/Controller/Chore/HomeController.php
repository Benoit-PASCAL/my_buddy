<?php

namespace App\Controller\Chore;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('chore/home/index.html.twig', [
            'title' => 'Hello',
        ]);
    }

    #[Route('/', name: 'app_under_construction')]
    public function default(): Response
    {
        return $this->render('chore/home/building.html.twig', [
            'title' => 'Website under construction' ,
            'message' => 'Please come back later.'
        ]);
    }
}
