<?php

namespace App\Chore\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RightsController extends AbstractController
{

    public function checkRights(int $access): void
    {
        $explodedClass = explode('\\', static::class);
        $controller = $explodedClass[array_key_last($explodedClass)];
        $class = str_replace('Controller', 's', $controller);

        $this->denyAccessUnlessGranted('ACCESS_' . $class . '_' . $access);
    }
}