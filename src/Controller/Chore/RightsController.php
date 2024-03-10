<?php

namespace App\Controller\Chore;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RightsController extends AbstractController
{
    public function checkRights(): void
    {
        if(!$this->isGranted('ROLE_USER')) {
            $this->redirectToRoute('app_login');
        }
    }
}