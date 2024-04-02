<?php

namespace App\Chore\Entity;

use App\Chore\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $profilePicture = null;

    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'appUser', cascade: ['persist'])]
    private Collection $assignments;

    #[ORM\Column(length: 70)]
    private ?string $secretKey = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();

        $this->lastName = '';
        $this->firstName = '';
        $this->phone = '';
        $this->profilePicture = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return str_replace('&#64;', '@', $this->email);
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        foreach ($this->assignments as $assignment) {
            /** @var Assignment $assignment */
            if(
                $assignment->hasStarted() &&
                !$assignment->hasEnded()
            ) {
                $roles[] = 'ROLE_' . strtoupper($assignment->getRole()->getLabel());
            }
        }

        return array_unique($roles);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function getCurrentAssignments(): Collection
    {
        $roles = new ArrayCollection();
        $roles_label = [];

        foreach ($this->assignments as $assignment) {
            /** @var Assignment $assignment */
            if(
                $assignment->getRole()->isActive() &&
                $assignment->hasStarted() &&
                !$assignment->hasEnded() &&
                !in_array($assignment->getRole()->getLabel(), $roles_label)
            ) {
                $roles[] = $assignment;
                $roles_label[] = $assignment->getRole()->getLabel();
            }
        }

        return $roles;
    }

    public function addAssignment(Assignment $assignments): static
    {
        if (!$this->assignments->contains($assignments)) {
            $this->assignments->add($assignments);
            $assignments->setAppUser($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignments): static
    {
        if ($this->assignments->removeElement($assignments)) {
            // set the owning side to null (unless already changed)
            if ($assignments->getAppUser() === $this) {
                $assignments->setAppUser(null);
            }
        }

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(string $secretKey): static
    {
        $this->secretKey = $secretKey;

        return $this;
    }
}
