<?php

namespace App\Entity;

use App\Repository\MatchUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: MatchUserRepository::class)]
class MatchUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user1 = null;

    #[ORM\ManyToOne(inversedBy: 'matchuser')]
    private ?MatchAccept $matchAccepted1 = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user2 = null;

    #[ORM\ManyToOne(inversedBy: 'matchuser')]
    private ?MatchAccept $matchAccepted2 = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private ?int $version = null;


    #[ORM\Column(type: 'datetime')]
    private $matchedAt;

    public function __construct()
    {

        $this->matchedAt = new \DateTime();
    }

    public function getMatchedAt(): ?\DateTimeInterface
    {
        return $this->matchedAt;
    }

    public function setMatchedAt(\DateTimeInterface $matchedAt): static
    {
        $this->matchedAt = $matchedAt;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getUser1(): ?User
    {
        return $this->user1;
    }

    public function setUser1(?User $user1): static
    {
        $this->user1 = $user1;

        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): static
    {
        $this->user2 = $user2;

        return $this;
    }

    public function getMatchAccepted1(): ?MatchAccept
    {
        return $this->matchAccepted1;
    }

    public function setMatchAccepted1(?MatchAccept $matchAccepted1): static
    {
        $this->matchAccepted1 = $matchAccepted1;

        return $this;
    }

    public function getMatchAccepted2(): ?MatchAccept
    {
        return $this->matchAccepted2;
    }

    public function setMatchAccepted2(?MatchAccept $matchAccepted2): static
    {
        $this->matchAccepted2 = $matchAccepted2;

        return $this;
    }


}
