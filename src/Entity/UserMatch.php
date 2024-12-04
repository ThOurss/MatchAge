<?php

namespace App\Entity;

use App\Repository\UserMatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserMatchRepository::class)]
class UserMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\Column]
    private string $status = '';
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: MatchUser::class, mappedBy: 'match')]
    private Collection $matchUsers;

    public function __construct()
    {
        $this->matchUsers = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, MatchUser>
     */
    public function getMatchUsers(): Collection
    {
        return $this->matchUsers;
    }

    public function addMatchUser(MatchUser $matchUser): static
    {
        if (!$this->matchUsers->contains($matchUser)) {
            $this->matchUsers->add($matchUser);
            $matchUser->setMatch($this);
        }

        return $this;
    }

    public function removeMatchUser(MatchUser $matchUser): static
    {
        if ($this->matchUsers->removeElement($matchUser)) {
            // set the owning side to null (unless already changed)
            if ($matchUser->getMatch() === $this) {
                $matchUser->setMatch(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
