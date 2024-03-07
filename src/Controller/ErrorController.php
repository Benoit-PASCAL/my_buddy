<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/403', name: 'app_403')]
    public function e403(): Response
    {
        return $this->render('home/error.html.twig', [
            'title' => '403 Error',
            'message' => 'You are not allowed to access this page.'
        ]);
    }

    #[Route('/404', name: 'app_404')]
    public function e404(): Response
    {
        return $this->render('home/error.html.twig', [
            'title' => '404 Error',
            'message' => 'The page you are looking for does not exist.'
        ]);
    }

    #[Route('/500', name: 'app_500')]
    public function e500(): Response
    {
        return $this->render('home/error.html.twig', [
            'title' => '500 Error',
            'message' => 'An error occurred while loading this page.'
        ]);
    }

    #[Route('/503', name: 'app_503')]
    public function e503(): Response
    {
        return $this->render('home/error.html.twig', [
            'title' => '503 Error',
            'message' => 'The server is currently unavailable.'
        ]);
    }
}
