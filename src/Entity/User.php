<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity('email', "Cet email ne peut être utilisé")] // pour ne pas dire qu'il est déjà utilisé
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email(message: "L'adresse email renseignée est invalide")]
    #[Assert\NotBlank]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Teacher $teacher = null;

    /**
     * @var Collection<int, PasswordResetRequest>
     */
    #[ORM\OneToMany(targetEntity: PasswordResetRequest::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $passwordResetRequests;

    public function __construct()
    {
        $this->passwordResetRequests = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->email ;        
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher): static
    {
        // set the owning side of the relation if necessary
        if ($teacher->getUser() !== $this) {
            $teacher->setUser($this);
        }

        $this->teacher = $teacher;

        return $this;
    }

    /**
     * @return Collection<int, PasswordResetRequest>
     */
    public function getPasswordResetRequests(): Collection
    {
        return $this->passwordResetRequests;
    }

    public function addPasswordResetRequest(PasswordResetRequest $passwordResetRequest): static
    {
        if (!$this->passwordResetRequests->contains($passwordResetRequest)) {
            $this->passwordResetRequests->add($passwordResetRequest);
            $passwordResetRequest->setUser($this);
        }

        return $this;
    }

    public function removePasswordResetRequest(PasswordResetRequest $passwordResetRequest): static
    {
        if ($this->passwordResetRequests->removeElement($passwordResetRequest)) {
            // set the owning side to null (unless already changed)
            if ($passwordResetRequest->getUser() === $this) {
                $passwordResetRequest->setUser(null);
            }
        }

        return $this;
    }
}
