<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $status_label = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusLabel(): ?string
    {
        return $this->status_label;
    }

    public function setStatusLabel(string $status_label): static
    {
        $this->status_label = $status_label;

        return $this;
    }
}
