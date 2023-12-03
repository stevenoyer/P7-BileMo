<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_detail_user",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 * 
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "app_delete_user",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 *
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getUsers', 'createUser'])]
    #[Assert\NotBlank(message: "L'adresse e-mail est obligatoire.")]
    #[Assert\Length(min: 1, max: 180, minMessage: "L'adresse e-mail doit faire au moins {{ limit }} caractères", maxMessage: "L'adresse e-mail ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['getUsers', 'createUser'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['getUsers', 'createUser'])]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getUsers', 'createUser'])]
    #[Assert\NotBlank(message: "Le nom de famille est obligatoire.")]
    #[Assert\Length(min: 1, max: 50, minMessage: "Le nom de famille doit faire au moins {{ limit }} caractères", maxMessage: "Le nom de famille ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $lastname = null;

    #[ORM\Column(length: 50)]
    #[Groups(['getUsers', 'createUser'])]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(min: 1, max: 50, minMessage: "Le prénom doit faire au moins {{ limit }} caractères", maxMessage: "Le prénom ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $firstname = null;

    #[ORM\ManyToOne(inversedBy: 'users', targetEntity: Customer::class)]
    #[Groups(['getUsers'])]
    private ?Customer $customer = null;

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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

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
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
