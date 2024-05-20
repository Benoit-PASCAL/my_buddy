<?php

namespace App\Chore\Entity;

use App\Chore\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use function PHPUnit\Framework\stringStartsWith;

/**
 * Status is a polymorphic entity that represents named objects.
 * It allows to create a list of named objects that can be used in the application and to
 * edit this list without changing the code.
 *
 * It is used to represent roles and controllers.
 *
 * It can be used to add other types of data that only need a label.
 * For example, it can be used to store the list of countries, the list of languages, etc.
 *
 * The type attribute can be used to differentiate the different types of data.
 * For example, the type can be used to store the type of the object (role, controller, country, language, etc.).
 * The type can be used to filter the list of objects in the UI and to apply specific behaviors.
 *
 * Statuses can store a color and an icon to be used in the UI.
 */
#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    public const ROLE_TYPE = 1;
    public const CONTROLLER_TYPE = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 8)]
    private ?string $color = null;

    #[ORM\Column(length: 100)]
    private ?string $icon = null;

    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'role')]
    private Collection $parent;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(targetEntity: Permission::class, mappedBy: 'role', cascade: ['persist'])]
    private Collection $permissions;

    public function __construct()
    {
        $this->parent = new ArrayCollection();
        $this->permissions = new ArrayCollection();

        // set default values
        $this->setActive(true);
        $this->setColor('000000');
        $this->setIcon('');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getFullColor(): ?string
    {
        return "#" . $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getFullIcon(): ?string
    {
        foreach (['bi', 'fa'] as $prefix) {
            if (stringStartsWith($prefix, $this->icon)) {
                return "<i class='$prefix $this->icon'> </i>";
            }
        }

        return '';
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getParents(): Collection
    {
        return $this->parent;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);

            if($this->getType() == self::ROLE_TYPE) {
                $permission->setRole($this);
            }

            if($this->getType() == self::CONTROLLER_TYPE) {
                $permission->setController($this);
            }
        }

        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getController() === $this) {
                $permission->setRole(null);
            }
        }

        return $this;
    }

    public static function getAttributes(): array
    {
        return [
            'label',
            'active',
        ];
    }
}
