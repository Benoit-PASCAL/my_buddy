<?php


namespace App\Tests\Chore\Security\Voter;

use App\Chore\Entity\Assignment;
use App\Chore\Entity\Permission;
use App\Chore\Entity\Status;
use App\Chore\Entity\User;
use App\Chore\Security\Voter\MainVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MainVoterTest extends TestCase
{
    public string $validController = Permission::CHORE_CONTROLLER_LIST[0];
    public string $nonExistingController = 'nonExistingController';
    public string $validAction = '1';
    public string $nonIntegerAction = 'view';

    public function testSupportsReturnsFalseForNonAccessAttributes()
    {
        $voter = new MainVoter();
        $this->assertFalse($voter->supports('NON_ACCESS_ATTRIBUTE', null));
    }

    public function testSupportsReturnsFalseForMalformedAttributes()
    {
        $voter = new MainVoter();
        $this->assertFalse($voter->supports('ACCESS_malformed', null));
    }

    public function testSupportsReturnsFalseForNonIntegerAction()
    {
        $voter = new MainVoter();
        $this->assertFalse($voter->supports('ACCESS_'. $this->validController . '_' . $this->nonIntegerAction , null));
    }

    public function testSupportsReturnsFalseForNonExistingController()
    {
        $voter = new MainVoter();
        $this->assertFalse($voter->supports('ACCESS_' . $this->nonExistingController . '_' . $this->validAction, null));
    }

    public function testSupportsReturnsTrueForValidAttributes()
    {
        $voter = new MainVoter();
        $this->assertTrue($voter->supports('ACCESS_' . $this->validController . '_' . $this->validAction, null));
    }

    public function testVoteOnAttributeReturnsFalseForUserWithoutAssignments()
    {
        $voter = new MainVoter();
        $token = $this->createMock(TokenInterface::class);
        $user = new User();
        $token->method('getUser')->willReturn($user);

        $this->assertFalse($voter->voteOnAttribute('ACCESS_' . $this->validController . '_' . $this->validAction, null, $token));
    }

    public function testVoteOnAttributeReturnsFalseForUserWithExpiredAssignments()
    {
        $voter = new MainVoter();
        $token = $this->createMock(TokenInterface::class);
        $user = new User();
        $assignment = (new Assignment())
            ->setStartDate(new \DateTimeImmutable('-2 days'))
            ->setEndDate(new \DateTimeImmutable('-1 day'));
        $user->addAssignment($assignment);
        $token->method('getUser')->willReturn($user);

        $this->assertFalse($voter->voteOnAttribute('ACCESS_' . $this->validController . '_' . $this->validAction, null, $token));
    }

    public function testVoteOnAttributeReturnsFalseForUserWithoutRequiredPermission()
    {
        $voter = new MainVoter();
        $token = $this->createMock(TokenInterface::class);
        $user = new User();
        $assignment = (new Assignment())
            ->setStartDate(new \DateTimeImmutable('-1 day'))
            ->setEndDate(new \DateTimeImmutable('+1 day'));
        $user->addAssignment($assignment);
        $token->method('getUser')->willReturn($user);

        $this->assertFalse($voter->voteOnAttribute('ACCESS_' . $this->validController . '_' . $this->validAction, null, $token));
    }

    public function testVoteOnAttributeReturnsTrueForUserWithRequiredPermission()
    {
        $voter = new MainVoter();
        $token = $this->createMock(TokenInterface::class);
        $user = new User();
        $assignment = (new Assignment())
            ->setStartDate(new \DateTimeImmutable('-1 day'))
            ->setEndDate(new \DateTimeImmutable('+1 day'));
        $role = (new Status())->setLabel('test');
        $assignment->setRole($role);
        $permission = (new Permission())
            ->setController((new Status())->setLabel($this->validController))
            ->setAccess(1);
        $assignment->getRole()->addPermission($permission);
        $user->addAssignment($assignment);
        $token->method('getUser')->willReturn($user);

        $this->assertTrue($voter->voteOnAttribute('ACCESS_' . $this->validController . '_' . $this->validAction, null, $token));
    }
}