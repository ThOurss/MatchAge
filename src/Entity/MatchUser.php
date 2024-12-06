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
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'matchesAsUser1')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user1 = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'matchesAsUser2')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user2 = null;



    #[ORM\Column(type: 'datetime')]
    private $matchedAt;

    public function __construct(?User $user1, ?User $user2)
    {
        $this->user1 = $user1;
        $this->user2 = $user2;
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


}
