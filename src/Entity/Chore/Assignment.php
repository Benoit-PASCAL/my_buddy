<?php

namespace App\Entity\Chore;

use App\Repository\Chore\AssignmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
class Assignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $appUser = null;

    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $role = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppUser(): ?User
    {
        return $this->appUser;
    }

    public function setAppUser(?User $appUser): static
    {
        $this->appUser = $appUser;

        return $this;
    }

    public function getRole(): ?Status
    {
        return $this->role;
    }

    public function setRole(?Status $role): static
    {
        $role
            ->setType(Status::ROLE_TYPE);
        $this->role = $role;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function hasStarted(): bool
    {
        return !$this->startDate->diff(new \DateTimeImmutable())->invert;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function hasEnded(): bool
    {
        return $this->endDate && !$this->endDate->diff(new \DateTimeImmutable())->invert;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
}
