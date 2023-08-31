<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table("task")
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    private int $id;


	/**
	 * @ORM\Column(type="datetime")
	 */
    private DateTime $createdAt;


	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank(message="Vous devez saisir un titre.")
	 */
    private string $title;


	/**
	 * @ORM\Column(type="text")
	 * @Assert\NotBlank(message="Vous devez saisir du contenu.")
	 */
    private string $content;


	/**
	 * @ORM\Column(type="boolean")
	 */
    private bool $isDone;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks")
     */
    private ?User $user;

    public function __construct()
    {
        $this->createdAt = new Datetime();
        $this->isDone = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): Datetime
	{
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function toggle($flag)
    {
        $this->isDone = $flag;
    }

    public function isDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
