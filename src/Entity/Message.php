<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "text", nullable: true)]
    private $contenue = null;

    #[ORM\Column(type: 'datetime')]
    private $dateEnvoie;

    #[ORM\ManyToOne(inversedBy: 'message')]
    private ?MessageStatut $statutMessage = null;

    #[ORM\ManyToMany(targetEntity: Conversation::class, inversedBy: 'message')]
    private Collection $conversation;

    #[ORM\ManyToOne(inversedBy: 'message')]
    private ?User $user = null;

    public function __construct()
    {
        $this->dateEnvoie = new \DateTime();
        $this->conversation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getStatutMessage(): ?MessageStatut
    {
        return $this->statutMessage;
    }

    public function setStatutMessage(?MessageStatut $statutMessage): static
    {
        $this->statutMessage = $statutMessage;

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversation(): Collection
    {
        return $this->conversation;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversation->contains($conversation)) {
            $this->conversation->add($conversation);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        $this->conversation->removeElement($conversation);

        return $this;
    }

    public function getDateEnvoie(): ?\DateTimeInterface
    {
        return $this->dateEnvoie;
    }

    public function setDateEnvoie(\DateTimeInterface $dateEnvoie): static
    {
        $this->dateEnvoie = $dateEnvoie;

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

    public function getContenue(): ?string
    {
        return $this->contenue;
    }

    public function setContenue(?string $contenue): static
    {
        $this->contenue = $contenue;

        return $this;
    }


}
