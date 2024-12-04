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
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'matchUsers')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: UserMatch::class, inversedBy: 'matchUsers')]
    private ?UserMatch $match = null;



    #[ORM\Column(type: 'datetime')]
    private $matchedAt;

    public function __construct(?User $user, ?UserMatch $match)
    {
        $this->user = $user;
        $this->match = $match;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMatch(): ?UserMatch
    {
        return $this->match;
    }

    public function setMatch(?UserMatch $match): static
    {
        $this->match = $match;

        return $this;
    }




}
