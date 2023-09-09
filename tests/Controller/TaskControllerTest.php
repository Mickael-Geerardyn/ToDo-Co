<?php

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Security\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
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
	private array $tasks;
	private Router $urlGenerator;

	public function setUp() : void

	{
		$this->client = static::createClient();

		$this->client->followRedirects();

		$this->userRepository = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository
		(User::class);

		$this->taskRepository = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository
		(Task::class);

		$this->tasks = $this->taskRepository->findAll();

		$this->user = $this->userRepository->findOneBy(["email" => "kelly.l@gmail.com"]);

		$this->urlGenerator = $this->client->getContainer()->get('router.default');

		$this->client->loginUser($this->user);
	}

	public function testGetTasks()
	{
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_list', [
			'users' => $this->tasks
		]));

		$this->assertResponseRedirects($this->urlGenerator->generate('task_list'), 200);
		$this->client->followRedirect();
	}

	public function testDeleteTaskAction()
	{
		$task = $this->taskRepository->findOneBy(["id" => $this->tasks[0]->getId()]);

		$token = new UsernamePasswordToken($this->user, 'main', ['memory']);

		$decisionManager = $this->client->getContainer()->get('security.access.decision_manager');

		$decision = $decisionManager->decide($token, $this->user->getRoles(), $task);

		$this->client->request(Request::METHOD_DELETE, $this->urlGenerator->generate('task_delete', [
			"id" => $this->tasks[0]->getId()
		]));

		if(!$decision)
		{
			$this->assertFalse($decision);
			$this->assertResponseRedirects($this->urlGenerator->generate('task_list'), 403);
			$this->client->followRedirect();
			$this->assertSelectorTextContains('div.alert.alert-danger', 'Vous ne pouvez pas supprimer cette tâche');
		}

		$this->assertTrue($decision);

		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
	}

	public function testEditTasks()
	{
		// First, call the task_edit page to return the edit page form in the crawler.
		$crawler =  $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_edit', [
			"id" => $this->tasks[0]->getId()
		]));
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());

		// Select the form with the button name and fill datas in the fiels
		$form = $crawler->selectButton("Modifier")->form();
		$form->setValues([
			"task[title]" => "Tâche de test pour la création",
			"task[content]" => "Contenu de la tâche de test pour la création"
						 ]);

		$this->client->submit($form);
		$this->client->followRedirect();
		$this->assertSelectorTextContains('div.alert.alert-success', 'La tâche a bien été modifiée.');
	}

	public function testCreateTasks()
	{
		// Same to edit, first, call the page web form
		$crawler =  $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_create'));
		$this->assertResponseIsSuccessful();
		$this->assertSelectorExists('form');

		$form = $crawler->selectButton("Ajouter")->form();
		//Add this "task[title]" in $form[] because "task[title]" is the name in the rendered field form name
		$form->setValues([
			"task[title]" => "Tâche de test pour la création",
			"task[content]" => "Contenu de la tâche de test pour la création"
						 ]);

		//The logged-in user is automatically added to the task in the controller.
		//If not logged in, access to the creation page is redirected to the login page.

		$this->client->submit($form);
		$this->assertResponseRedirects($this->urlGenerator->generate('user_create'));
		$this->assertSelectorTextContains('div.alert.alert-success', "Votre compte vient d'être créer avec succès");
		$this->client->followRedirect();
	}

	public function testToggleTaskAction()
	{
		$isDone = $this->tasks[6]->isDone();
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('task_toggle', ['id' => $this->tasks[0]->getId()]));
		$this->client->followRedirect();

		if($isDone)		{
			$this->assertSelectorTextContains('div.alert.alert-success', 'La tâche '.$this->tasks[0]->getTitle().' a bien été marquée comme terminée.');
		} elseif (!$isDone) {
			$this->assertSelectorTextContains('div.alert.alert-success', 'La tâche ' . $this->tasks[0]->getTitle() . ' a bien été marquée comme non terminée.');
		}


	}

}