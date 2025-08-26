<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $start_datetime = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $registration_deadline = null;

    #[ORM\Column(nullable: true)]
    private ?int $max_registrations = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $event_state = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_url = null;

    #[ORM\Column]
    private ?int $organizer = null;

    #[ORM\Column]
    private ?int $place = null;

    #[ORM\Column]
    private ?int $state = null;

    #[ORM\ManyToOne(inversedBy: 'sortiesOrganisees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'sortie', orphanRemoval: true)]
    private Collection $inscriptions;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cancellationReason = null;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
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
        return $this->start_datetime;
    }

    public function setStartDatetime(\DateTime $start_datetime): static
    {
        $this->start_datetime = $start_datetime;

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
        return $this->registration_deadline;
    }

    public function setRegistrationDeadline(?\DateTime $registration_deadline): static
    {
        $this->registration_deadline = $registration_deadline;

        return $this;
    }

    public function getMaxRegistrations(): ?int
    {
        return $this->max_registrations;
    }

    public function setMaxRegistrations(?int $max_registrations): static
    {
        $this->max_registrations = $max_registrations;

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

    public function getEventState(): ?int
    {
        return $this->event_state;
    }

    public function setEventState(?int $event_state): static
    {
        $this->event_state = $event_state;

        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photo_url;
    }

    public function setPhotoUrl(?string $photo_url): static
    {
        $this->photo_url = $photo_url;

        return $this;
    }

    public function getOrganizer(): ?int
    {
        return $this->organizer;
    }

    public function setOrganizer(int $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(int $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getSortie() === $this) {
                $inscription->setSortie(null);
            }
        }

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
}
