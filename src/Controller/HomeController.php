<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'title' => 'Hello',
        ]);
    }

    #[Route('/', name: 'app_under_construction')]
    public function default(): Response
    {
        return $this->render('home/building.html.twig', [
            'title' => 'Website under construction' ,
            'message' => 'Please come back later.'
        ]);
    }
}
