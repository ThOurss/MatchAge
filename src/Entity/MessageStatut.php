<?php

namespace App\Entity;

use App\Repository\MessageStatutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageStatutRepository::class)]
class MessageStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $statutName = null;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'statutMessage')]
    private Collection $message;

    public function __construct()
    {
        $this->message = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatutName(): ?string
    {
        return $this->statutName;
    }

    public function setStatutName(string $statutName): static
    {
        $this->statutName = $statutName;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessage(): Collection
    {
        return $this->message;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->message->contains($message)) {
            $this->message->add($message);
            $message->setStatutMessage($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->message->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getStatutMessage() === $this) {
                $message->setStatutMessage(null);
            }
        }

        return $this;
    }
}
