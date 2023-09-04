<?php

namespace Tests\Controller;

use App\Controller\TaskController;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultControllerTest extends WebTestCase
{
    public function testAddUserToTask(UserRepository $userRepository)
    {
		$client = static::createClient();
		$crawler = $client->request("POST", '/tasks/create');

		$user = $userRepository->find(1);

		$task = new Task();
		$task->setTitle("Titre de test");
		$task->setContent("Description de test");
		$task->setUser($user);


        $this->assertSame($task, $client->getResponse()->getStatusCode());
    }
}
