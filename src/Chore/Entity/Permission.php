<?php

namespace App\Chore\Entity;

use App\Chore\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{
    public const CAN_NOTHING = 0;
    public const CAN_VIEW = 1;
    public const CAN_EDIT = 2;
    public const CAN_DELETE = 3;
    public const CAN_CREATE = 4;
    public const CAN_ALL = 5;
    public const CONTROLLER_LIST = [
        'Users',
        'Roles',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $access = null;

    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $controller = null;

    #[ORM\ManyToOne(inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccess(): ?int
    {
        return $this->access;
    }

    public function setAccess(int $access): static
    {
        $this->access = $access;

        return $this;
    }

    public function getController(): ?Status
    {
        return $this->controller;
    }

    public function setController(?Status $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function getRole(): ?Status
    {
        return $this->role;
    }

    public function setRole(?Status $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function __toString(): string
    {
        return match($this->access) {
            self::CAN_NOTHING => 'No Access',
            self::CAN_VIEW => 'Can View',
            self::CAN_EDIT => 'Can Edit',
            self::CAN_DELETE => 'Can Delete',
            self::CAN_CREATE => 'Can Create',
            self::CAN_ALL => 'Full Access',
            default => throw new \Error('This code should not be reached!')
        };
    }
}
