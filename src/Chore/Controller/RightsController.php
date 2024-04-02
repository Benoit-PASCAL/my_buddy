<?php

namespace App\Chore\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * RightsController is a controller that handles rights related actions.
 * It extends the AbstractController to use Symfony's base controller functionalities.
 */
class RightsController extends AbstractController
{

    /**
     * Checks if the current user has the required access rights.
     *
     * @param int $access The required access level.
     * @throws AccessDeniedException If the user does not have the required access rights.
     */
    public function checkRights(int $access): void
    {
        // Get the name of the current controller
        $explodedClass = explode('\\', static::class);
        $controller = $explodedClass[array_key_last($explodedClass)];

        // Remove 'Controller' from the name and add 's' to make it plural
        $class = str_replace('Controller', 's', $controller);

        // Deny access if the user does not have the required rights
        $this->denyAccessUnlessGranted('ACCESS_' . $class . '_' . $access);
    }
}