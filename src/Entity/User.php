<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ["email"],
    message: "Cette adresse email est déjà utilisée."
)]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre prénom'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre nom'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre date de naissance'
    )]
    #[Assert\LessThan(
        value: '-15 years',
        message: 'Vous devez avoir au moins 16 ans'
    )]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $age;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^(\+33|0)[1-9](\d{2}){4}$/',
        message: "Le numéro de téléphone n'est pas valide"
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre adresse email'
    )]
    #[Assert\Email(
        message: "L'adresse email '{{ value }}' n'est pas valide."
    )]
    private ?string $email = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre mot de passe'
    )]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le mot de passe est trop long."
    )]
    #[Assert\Regex(
        pattern: "/[A-Z]/",
        message: "Le mot de passe doit contenir au moins une lettre majuscule."
    )]
    #[Assert\Regex(
        pattern: "/[a-z]/",
        message: "Le mot de passe doit contenir au moins une lettre minuscule."
    )]
    #[Assert\Regex(
        pattern: "/\d/",
        message: "Le mot de passe doit contenir au moins un chiffre."
    )]
    #[Assert\Regex(
        pattern: "/[\W]/",
        message: "Le mot de passe doit contenir au moins un caractère spécial (par exemple, @, #, $, etc.)."
    )]
    private ?string $password = null;


    #[ORM\OneToMany(targetEntity: MatchUser::class, mappedBy: 'user')]
    private Collection $matchUsers;

    #[ORM\Column(type: 'boolean')]
    private $isSearching = false;
    #[ORM\Column(type: 'boolean')]
    private $searchComplete = true;

    #[ORM\ManyToOne(inversedBy: 'user')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;
    #[ORM\ManyToOne(inversedBy: 'user')]
    private ?Civilite $civilite = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    private ?Pays $pays = null;

    #[ORM\OneToMany(targetEntity: Signalement::class, mappedBy: 'user')]
    private Collection $signalements;

    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: 'user')]
    private Collection $conversation;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'user')]
    private Collection $message;

    public function __construct()
    {
        $this->signalements = new ArrayCollection();
        $this->conversation = new ArrayCollection();
        $this->matchUsers = new ArrayCollection();
        $this->message = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }


    public function getPays(): ?Pays
    {
        return $this->pays;
    }

    public function setPays(?Pays $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * @return Collection<int, Signalement>
     */
    public function getSignalements(): Collection
    {
        return $this->signalements;
    }

    public function addSignalement(Signalement $signalement): static
    {
        if (!$this->signalements->contains($signalement)) {
            $this->signalements->add($signalement);
            $signalement->setUser($this);
        }

        return $this;
    }

    public function removeSignalement(Signalement $signalement): static
    {
        if ($this->signalements->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getUser() === $this) {
                $signalement->setUser(null);
            }
        }

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
            $conversation->addUser($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversation->removeElement($conversation)) {
            $conversation->removeUser($this);
        }

        return $this;
    }

    public function getCivilite(): ?Civilite
    {
        return $this->civilite;
    }

    public function setCivilite(?Civilite $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }


    public function getRoles(): array
    {
        return [$this->role->getRoleName()];
    }


    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function isSearching(): ?bool
    {
        return $this->isSearching;
    }

    public function setSearching(bool $isSearching): static
    {
        $this->isSearching = $isSearching;

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
            $matchUser->setUser($this);
        }

        return $this;
    }

    public function removeMatchUser(MatchUser $matchUser): static
    {
        if ($this->matchUsers->removeElement($matchUser)) {
            // set the owning side to null (unless already changed)
            if ($matchUser->getUser() === $this) {
                $matchUser->setUser(null);
            }
        }

        return $this;
    }

    public function isSearchComplete(): ?bool
    {
        return $this->searchComplete;
    }

    public function setSearchComplete(bool $searchComplete): static
    {
        $this->searchComplete = $searchComplete;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;
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
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->message->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }


}
