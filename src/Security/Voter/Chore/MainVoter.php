<?php

namespace App\Security\Voter\Chore;

use App\Entity\Chore\Assignment;
use App\Entity\Chore\Permission;
use App\Entity\Chore\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MainVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!str_contains($attribute, '_')) {
            return false;
        }

        $controller = strtolower(explode('_', $attribute)[0]);
        $action = explode('_', $attribute)[1] - 0;

        if(!is_int($action)) {
            return false;
        }

        if(!in_array($controller, array_map(function($item) {
            return strtolower($item); } ,Permission::CONTROLLER_LIST))) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $controller = strtolower(explode('_', $attribute)[0]);
        $action = explode('_', $attribute)[1] - 0;

        $user = $token->getUser();


        /** @var $user User */
        foreach ($user->getAssignments() as $assignment) {
            /** @var $assignment Assignment */
            foreach ($assignment->getRole()->getPermissions() as $permission) {
                /** @var $permission Permission */
                if (strtolower($permission->getController()->getLabel()) === $controller) {
                    if ($permission->getAccess() >= $action) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
