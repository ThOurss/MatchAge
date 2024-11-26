<?php

namespace App\Entity;

use App\Repository\SignalementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignalementRepository::class)]
class Signalement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSignalement = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?SignalementCategorie $signalementCategorie  = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateSignalement(): ?\DateTimeInterface
    {
        return $this->dateSignalement;
    }

    public function setDateSignalement(\DateTimeInterface $dateSignalement): static
    {
        $this->dateSignalement = $dateSignalement;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSignalementCategorie(): ?SignalementCategorie
    {
        return $this->signalementCategorie;
    }

    public function setSignalementCategorie(?SignalementCategorie $signalementCategorie): static
    {
        $this->signalementCategorie = $signalementCategorie;

        return $this;
    }
}
