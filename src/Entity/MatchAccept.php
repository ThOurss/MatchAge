<?php

namespace App\Entity;

use App\Repository\MatchAcceptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchAcceptRepository::class)]
class MatchAccept
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelleAccept = null;

    #[ORM\OneToMany(targetEntity: MatchUser::class, mappedBy: 'acceptmatch')]
    private Collection $matchUser;

    public function __construct()
    {
        $this->matchUser = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleAccept(): ?string
    {
        return $this->libelleAccept;
    }

    public function setLibelleAccept(string $libelleAccept): static
    {
        $this->libelleAccept = $libelleAccept;

        return $this;
    }

    /**
     * @return Collection<int, MatchUser>
     */
    public function getMatchUser(): Collection
    {
        return $this->matchUser;
    }

    public function addMatchUser(MatchUser $matchUser): static
    {
        if (!$this->matchUser->contains($matchUser)) {
            $this->matchUser->add($matchUser);
            $matchUser->setAcceptmatch($this);
        }

        return $this;
    }

    public function removeMatchUser(MatchUser $matchUser): static
    {
        if ($this->matchUser->removeElement($matchUser)) {
            // set the owning side to null (unless already changed)
            if ($matchUser->getAcceptmatch() === $this) {
                $matchUser->setAcceptmatch(null);
            }
        }

        return $this;
    }


}
