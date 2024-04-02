<?php

namespace App\Chore\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ErrorController is a controller that handles error page related actions and override Symfony default error pages.
 * It extends the AbstractController to use Symfony's base controller functionalities.
 */
class ErrorController extends AbstractController
{
    /**
     * Display the 403 error page.
     *
     * @Route('/403', name: 'app_403')
     * @return Response
     */
    #[Route('/403', name: 'app_403')]
    public function e403(): Response
    {
        return $this->render('chore/home/error.html.twig', [
            'title' => '403 Error',
            'message' => 'You are not allowed to access this page.'
        ]);
    }

    /**
     * Display the 404 error page.
     *
     * @Route('/404', name: 'app_404')
     * @return Response
     */
    #[Route('/404', name: 'app_404')]
    public function e404(): Response
    {
        return $this->render('chore/home/error.html.twig', [
            'title' => '404 Error',
            'message' => 'The page you are looking for does not exist.'
        ]);
    }

    /**
     * Display the 500 error page.
     *
     * @Route('/500', name: 'app_500')
     * @return Response
     */
    #[Route('/500', name: 'app_500')]
    public function e500(): Response
    {
        return $this->render('chore/home/error.html.twig', [
            'title' => '500 Error',
            'message' => 'An error occurred while loading this page.'
        ]);
    }

    /**
     * Display the 503 error page.
     *
     * @Route('/503', name: 'app_503')
     * @return Response
     */
    #[Route('/503', name: 'app_503')]
    public function e503(): Response
    {
        return $this->render('chore/home/error.html.twig', [
            'title' => '503 Error',
            'message' => 'The server is currently unavailable.'
        ]);
    }
}
