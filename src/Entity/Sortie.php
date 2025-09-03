<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
//#[ORM\HasLifecycleCallbacks]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le nom de la sortie est obligatoire.')]
    #[Assert\Length(min: 2, max: 30, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La date de début est obligatoire.')]
    #[Assert\GreaterThan(propertyPath: 'registration_deadline', message: 'La date de sortie doit être après la date limite d\'inscription.')]
    #[Assert\GreaterThanOrEqual('+2 days', message: 'La date de début doit être au moins 2 jours après aujourd\'hui.')]
    private ?\DateTime $startDatetime = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La durée est obligatoire.')]
    #[Assert\Positive(message: 'La durée doit être un nombre positif.')]
    private ?int $duration = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual('now', message: 'La date limite d\'inscription ne peut pas être dans le passé.')]
    #[Expression("this.getRegistrationDeadline() < this.getStartDatetime().modify('-48 hours')",
        message: 'La date limite d\'inscription doit être au moins 48 heures avant le début de la sortie.')]
    private ?\DateTime $registrationDeadline = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le nombre d\'inscriptions doit être un nombre positif.')]
    private ?int $maxRegistrations = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUrl = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Le lieu est obligatoire.')]
    private ?Place $place = null;


    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cancellationReason = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sortiesInscrit')]
    private Collection $users;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publicationDate = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDatetime(): ?\DateTime
    {
        return $this->startDatetime;
    }

    public function setStartDatetime(\DateTime $startDatetime): static
    {
        $this->startDatetime = $startDatetime;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTime
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(?\DateTime $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->maxRegistrations;
    }

    public function setMaxRegistrations(?int $maxRegistrations): static
    {
        $this->maxRegistrations = $maxRegistrations;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }


    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): static
    {
        $this->photoUrl = $photoUrl;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(?string $cancellationReason): static
    {
        $this->cancellationReason = $cancellationReason;

        return $this;
    }

    public function getOrganizer (): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addSortiesInscrit($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);
        $user->removeSortiesInscrit($this);

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeImmutable
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeImmutable $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }
}
