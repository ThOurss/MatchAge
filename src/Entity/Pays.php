<?php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
class Pays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private ?string $namePays = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $codeIso2 = null;

    #[ORM\Column(length: 6)]
    private ?string $codeIso3 = null;

    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'pays')]
    private Collection $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNamePays(): ?string
    {
        return $this->namePays;
    }

    public function setNamePays(string $namePays): static
    {
        $this->namePays = $namePays;

        return $this;
    }

    public function getCodeIso2(): ?string
    {
        return $this->codeIso2;
    }

    public function setCodeIso2(?string $codeIso2): static
    {
        $this->codeIso2 = $codeIso2;

        return $this;
    }

    public function getCodeIso3(): ?string
    {
        return $this->codeIso3;
    }

    public function setCodeIso3(string $codeIso3): static
    {
        $this->codeIso3 = $codeIso3;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setPays($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getPays() === $this) {
                $user->setPays(null);
            }
        }

        return $this;
    }
}
