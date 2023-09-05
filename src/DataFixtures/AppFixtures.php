<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
	private Task $task;
	private array $users =
		[
			["email" => "contact@mickael-geerardyn.com", "name" => "Mickaël", "password" => "password", "role" => "ROLE_ADMIN"],
			["email" => "kelly.l@gmail.com", "name" => "Kelly", "password" => "password", "role" => "ROLE_USER"],
			["email" => "freddy.g@gmail.com", "name" => "Freddy", "password" => "password", "role" => "ROLE_USER"]
		];
	private array $objectUsersArray;

	public function __construct(
		private readonly UserPasswordHasherInterface $userPasswordHasher
	)
	{
		$this->objectUsersArray = array();
	}
    public function load(ObjectManager $manager): void
    {
		self::loadUser($manager);
		self::loadTask($manager);

        $manager->flush();
    }

	public function loadUser(ObjectManager $manager): void
	{
		foreach($this->users as $user)
		{
			$user1 = new User();
			$user1->setUsername($user["name"]);
			$user1->setEmail($user["email"]);
			$user1->setPassword($this->userPasswordHasher->hashPassword($user1, $user["password"]));
			$user1->setRoles([$user["role"]]);

			$this->objectUsersArray[] = $user1;

			$manager->persist($user1);
		}
	}

	public function loadTask(ObjectManager $manager): void
	{
		$lengthUserArray = count($this->objectUsersArray) -1;
		$imageNumber = 0;

		for($i = 0; $i < 60; $i++)
		{

			$selectedUserObject = $this->objectUsersArray[mt_rand(0, $lengthUserArray)];

			$this->task = new Task();

			$this->task->setTitle("Titre de la tâche numéro ${i}");
			$this->task->setContent("Description de la tâche numéro ${i}");
			$this->task->setUser($selectedUserObject);

			$manager->persist($this->task);
		}
	}

}
