<?php

namespace App\Controller\Chore;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RightsController extends AbstractController
{

    public function checkRights(int $access): void
    {
        $explodedClass = explode('\\', static::class);
        $controller = $explodedClass[array_key_last($explodedClass)];
        $class = str_replace('Controller', 's', $controller);

        $this->denyAccessUnlessGranted($class . '_' . $access);
    }
}