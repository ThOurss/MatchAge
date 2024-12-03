<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre prénom'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre nom'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre date de naissance'
    )]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 250, unique:true)]
    #[Assert\NotBlank(
        message: 'Veuillez renseigner votre adresse email'
    )]
    #[Assert\Email(
         message:"L'adresse email '{{ value }}' n'est pas valide."
     )]
    private ?string $email = null;


    #[ORM\Column(length: 250)]
    #[Assert\Length(
     min: 8,
     max: 4096,
     minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.",
     maxMessage: "Le mot de passe est trop long."
    )]
    #[Assert\Regex(
         pattern:"/[A-Z]/",
         message:"Le mot de passe doit contenir au moins une lettre majuscule."
     )]
     #[Assert\Regex(
         pattern:"/[a-z]/",
         message:"Le mot de passe doit contenir au moins une lettre minuscule."
     )]
     #[Assert\Regex(
         pattern:"/\d/",
         message:"Le mot de passe doit contenir au moins un chiffre."
     )]
     #[Assert\Regex(
         pattern:"/[\W]/",
         message:"Le mot de passe doit contenir au moins un caractère spécial (par exemple, @, #, $, etc.)."
     )]
    private ?string $password = null;
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $matchedUser;

    #[ORM\Column(type: 'boolean')]
    private $isSearching = false;

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



    public function __construct()
    {
        $this->signalements = new ArrayCollection();
        $this->conversation = new ArrayCollection();
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
        return [$this->role?->getRoleName() ?? 'ROLE_USER'];
    }


    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function getMatchedUser(): ?self
    {
        return $this->matchedUser;
    }

    public function setMatchedUser(?self $matchedUser): static
    {
        $this->matchedUser = $matchedUser;

        return $this;
    }


}
