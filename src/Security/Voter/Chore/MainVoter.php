<?php

namespace App\Security\Voter\Chore;

use App\Chore\Entity\Assignment;
use App\Chore\Entity\Permission;
use App\Chore\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MainVoter extends Voter
{
    public function supports(string $attribute, mixed $subject): bool
    {
        // only vote on `ACCESS_` attributes
        if (!str_starts_with($attribute, 'ACCESS_')) {
            return false;
        }

        // control if the attribute is well formatted => ACCESS_controller_action
        $access = explode('_', $attribute);
        if(count($access) !== 3) {
            return false;
        }

        $controller = strtolower($access[1]);
        $action = $access[2];

        // control if the action is well formatted => integer
        if(!ctype_digit($action)) {
            return false;
        }

        // control if the controller is well formatted => in controllers list
        if(!in_array($controller, array_map(function($item) {
            return strtolower($item); } ,Permission::CONTROLLER_LIST))) {
            return false;
        }

        return true;
    }

    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $controller = strtolower(explode('_', $attribute)[1]);
        $action = intval(explode('_', $attribute)[2]);

        $user = $token->getUser();

        /** @var $user User */
        foreach ($user->getAssignments() as $assignment) {
            /** @var $assignment Assignment */
            if($assignment->getStartDate() < new \DateTime() &&
                ($assignment->getEndDate() === null || $assignment->getEndDate() > new \DateTime())) {
                if($assignment->getRole()) {
                    foreach ($assignment->getRole()->getPermissions() as $permission) {
                        /** @var $permission Permission */
                        if (strtolower($permission->getController()->getLabel()) === $controller &&
                            $permission->getAccess() >= $action) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
