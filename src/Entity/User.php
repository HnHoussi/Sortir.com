<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity('email', message: 'Cet email est déjà utilisé.')]
#[UniqueEntity('pseudo', message: 'Ce pseudo est déjà utilisé.')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'adresse email est obligatoire.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    #[Assert\Regex(pattern: "/@campus-eni\.fr$/", message: "L'adresse email doit se terminer par @campus-eni.fr")]
    private string $email;

    #[ORM\Column]
    private array $roles = [];

    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.", groups: ['user_create'])]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères.", groups: ['user_create'])]
    #[ORM\Column]
    private ?string $password;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank(message: "Le pseudo est obligatoire.")]
    #[Assert\Length(max: 30, maxMessage: "Le pseudo ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9._-]+$/', message: "Le pseudo ne peut contenir que lettres, chiffres, points, tirets et underscores.")]
    private string $pseudo;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private string $lastName;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    private string $firstName;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^\+?[0-9\s-]{6,20}$/', message: "Le numéro de téléphone n'est pas valide.")]
    private ?string $phone = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le campus est obligatoire.")]
    private ?Campus $campus = null;

    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'organizer', orphanRemoval: true)]
    private Collection $sortiesOrganisees;

    #[ORM\ManyToMany(targetEntity: Sortie::class, mappedBy: 'users')]
    private Collection $sortiesInscrit;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->sortiesOrganisees = new ArrayCollection();
        $this->sortiesInscrit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = trim($email);

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = trim($pseudo);

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = trim($lastName);

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = trim($firstName);

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = trim($phone);

        return $this;
    }

//    public function isAdministrator(): ?bool
//    {
//        return $this->administrator;
//    }
//
//    public function setAdministrator(bool $administrator): static
//    {
//        $this->administrator = $administrator;
//
//        return $this;
//    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }


    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisees(): Collection
    {
        return $this->sortiesOrganisees;
    }

    public function addSortiesOrganisee(Sortie $sortiesOrganisee): static
    {
        if (!$this->sortiesOrganisees->contains($sortiesOrganisee)) {
            $this->sortiesOrganisees->add($sortiesOrganisee);
            $sortiesOrganisee->setOrganizer($this);
        }

        return $this;
    }

    public function removeSortiesOrganisee(Sortie $sortiesOrganisee): static
    {
        if ($this->sortiesOrganisees->removeElement($sortiesOrganisee)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisee->getOrganizer () === $this) {
                $sortiesOrganisee->setOrganizer (null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesInscrit(): Collection
    {
        return $this->sortiesInscrit;
    }

    public function addSortiesInscrit(Sortie $sortiesInscrit): static
    {
        if (!$this->sortiesInscrit->contains($sortiesInscrit)) {
            $this->sortiesInscrit->add($sortiesInscrit);
            $sortiesInscrit->addUser($this);
        }

        return $this;
    }

    public function removeSortiesInscrit(Sortie $sortiesInscrit): static
    {
        if ($this->sortiesInscrit->removeElement($sortiesInscrit)) {
            $sortiesInscrit->removeUser($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return ($this->firstName ?? '') . ' ' . ($this->lastName ?? '');
    }

}
