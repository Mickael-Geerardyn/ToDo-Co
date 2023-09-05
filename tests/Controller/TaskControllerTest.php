<?php

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Security\TaskVoter;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TaskControllerTest extends WebTestCase
{
	private KernelBrowser|null $client = null;
	private UserRepository $userRepository;
	private TaskRepository $taskRepository;
	private User $user;
	private Router $urlGenerator;
	private TaskVoter $taskVoter;
	private const TASK_ID = 13;
	public function setUp() : void

	{
		$this->client = static::createClient();

		$this->userRepository = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository
		(User::class);

		$this->taskRepository = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository
		(Task::class);

		$this->user = $this->userRepository->findOneBy(["email" => "kelly.l@gmail.com"]);

		$this->urlGenerator = $this->client->getContainer()->get('router.default');

		$this->client->loginUser($this->user);
	}
	public function testDeleteTaskAction()
	{
		$task = $this->taskRepository->findOneBy(["id" => self::TASK_ID]);

		$token = new UsernamePasswordToken($this->user,  'credentials', 'memory');
		$decisionManager = $this->client->getContainer()->get('security.access.decision_manager');

		$decision = $decisionManager->decide($token, $this->user->getRoles(), $task);

		$this->assertTrue($decision);

		$crawler = $this->client->request(Request::METHOD_DELETE, $this->urlGenerator->generate('task_delete', [
			"id" => self::TASK_ID
		]));

		$this->client->followRedirect();

		$this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
	}
}