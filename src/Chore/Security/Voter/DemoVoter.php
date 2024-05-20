<?php

namespace App\Chore\Security\Voter;

use App\Chore\Entity\Assignment;
use App\Chore\Entity\Permission;
use App\Chore\Entity\User;
use App\Chore\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use function Symfony\Component\Translation\t;

/**
 * MainVoter is a voter that handles access control related actions.
 * It extends the Voter to use Symfony's base voter functionalities.
 */
class DemoVoter extends Voter
{
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Check if the voter supports the attribute and subject.
     *
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    public function supports(string $attribute, mixed $subject): bool
    {
        // only vote on `IS_DEMO` attributes
        if ($attribute != 'IS_DEMO') {
            return false;
        }

        return true;
    }

    /**
     * Vote on the attribute based on the user's permissions.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    public function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if(!$this->userRepo->findOneBy(['email' => 'demo@mybuddy.com'])) {
            return false;
        }

        // TODO: Add Setting to enable/disable demo mode

        return true;
    }
}
