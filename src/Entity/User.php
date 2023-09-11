<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table("user")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: "email",message: "Cette adresse courriel est déjà utilisée")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[ORM\Column]
	#[ORM\Id]
	#[ORM\GeneratedValue]
    private ?int $id = null;


	#[ORM\Column(type: Types::STRING, length: 25, unique: true)]
	#[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private string $username;

	#[ORM\Column(type: Types::STRING, length: 64)]
	#[Assert\NotBlank(message: 'Veuillez entrer un mot de passe valide', groups: ["Registration"])]
	#[Assert\Length(min: 12, max: 64, minMessage: 'Votre mot de passe doit contenir au minimum 8 caractères',
		groups: ["Registration"])]
	#[Assert\Regex(
		pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
		message: 'Le mot de passe doit comporter au moins 12 caractères, contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.'
	)]
    private string $password;

	#[ORM\Column(type: Types::STRING, length: 60, unique: true)]
	#[Assert\NotBlank(message: "Vous devez saisir une adresse email.")]
	#[Assert\Email(message: "Le format de l'adresse n'est pas correcte")]
    private string $email;

	#[ORM\OneToMany(mappedBy: "user", targetEntity: Task::class)]
    private Collection $tasks;

	#[ORM\Column(type: "json")]
	private string|array $roles;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
	{
        return $this->username;
    }

    public function setUsername($username): void
	{
        $this->username = $username;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getPassword(): ?string
	{
        return $this->password;
    }

    public function setPassword($password): void
	{
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail($email): void
	{
        $this->email = $email;
    }

	public function getRoles(): string|array
	{
		$roles[] = $this->roles;

		if(!$roles)
		{
			$roles = ["ROLE_USER"];
		}

		return array_unique($roles);
	}

	public function setRoles(string $roles): self
	{
		$this->roles = $roles;

		return $this;
	}

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
		return $this->email;
    }

	public function getTasks(): ?Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        // set the owning side to null (unless already changed)
        if ($this->tasks->removeElement($task) && $task->getUser() === $this) {
            $task->setUser(null);
        }

        return $this;
    }
}
