<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UtilisateurRepository;
use App\State\UserPasswordHasher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ApiResource(operations: [new GetCollection(), new Get(),
    new Delete(security: "is_granted('ROLE_USER') and object == user"),
    new Post(denormalizationContext: ["groups" => ["utilisateur:create"]], validationContext: ["utilisateur:create", "Default"], processor: UserPasswordHasher::class),
    new Patch(denormalizationContext: ["groups" => ["utilisateur:update"]], security: "is_granted('ROLE_USER') and object == user", validationContext: ["utilisateur:update", "Default"], processor: UserPasswordHasher::class)],
    normalizationContext: ["groups" => ["utilisateur:read"]],
    order: ["login" => "DESC"])]
#[UniqueEntity(['login', 'mail'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['utilisateur:read', 'utilisateur:create'])]
    #[Assert\Length(min: 3, max: 20)]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    #[ORM\Column(length: 20, unique: true)]
    private ?string $login = null;

    #[Groups(['utilisateur:read', 'utilisateur:create', 'utilisateur:update'])]
    #[Assert\Email]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[ApiProperty(readable: false, writable: false)]
    private ?string $password = null;

    #[Groups(['utilisateur:create', 'utilisateur:update'])]
    #[Assert\Length(min: 8, max: 30)]
    #[Assert\Regex("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,30}$#")]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'Auteur', targetEntity: Publication::class, orphanRemoval: true)]
    private Collection $publications;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): self
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setAuteur($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getAuteur() === $this) {
                $publication->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     */
    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

}
