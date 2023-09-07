<?php

namespace Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

class UserControllerTest extends WebTestCase
{
	private KernelBrowser|null $client = null;
	private UserRepository $userRepository;
	private UserPasswordHasherInterface $passwordHasher;
	private User $user;
	private User $admin;
	private Router $urlGenerator;
	private AccessDecisionManager $decisionManager;
	private const USER_ROLES = ["ROLE_ADMIN", "ROLE_USER"];
	private const USER_ID = 4;
	private const ADMIN_ID = 1;
	public function setUp() : void

	{
		$this->client = static::createClient();

		$this->client->followRedirects();

		$this->newUser = new User();

		$this->userRepository = $this->client->getContainer()->get("doctrine.orm.entity_manager")->getRepository
		(User::class);

		$this->passwordHasher = $this->client->getContainer()->get("security.password_hasher");

		$this->user = $this->userRepository->findOneBy(["email" => "kelly.l@gmail.com"]);

		$this->admin = $this->userRepository->findOneBy(["id" => 1]);

		$this->urlGenerator = $this->client->getContainer()->get('router.default');
	}

	public function testGetUsers()
	{
		$this->client->loginUser($this->user);

		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_list', [
			'users' => $this->userRepository->findAll()
		]));

		$this->assertResponseIsSuccessful();
		$this->client->followRedirect();
	}

	public function testCreateUser()
	{
		// Same to edit, first, call the page web form
		$crawler =  $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_create'));
		$this->assertResponseIsSuccessful();
		$this->assertSelectorExists('form');

		$form = $crawler->selectButton("Ajouter")->form();

		//Add this "user[username]" in $form[] because "user[username]" is the name in the rendered field form name
		$form["user[username]"] = "phpUnit5";
		$form["user[password][first]"] = "password";
		$form["user[password][second]"] = "password";
		$form["user[email]"] = "phpunit5@gmail.com";
		$form["user[roles]"] = "ROLE_ADMIN";

		//The logged-in user is automatically added to the task in the controller.
		//If not logged in, access to the creation page is redirected to the login page.

		$this->client->submit($form);
		$this->assertResponseRedirects($this->urlGenerator->generate('user_list'), 302);
		$this->client->followRedirect();
	}

	public function testEditUserWithoutLogin()
	{
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', [
			'id' => self::USER_ID
		]));

		$this->assertResponseRedirects($this->urlGenerator->generate('home_page'), 302);
		$this->client->followRedirect();
	}

	public function testEditUserRoleUser()
	{
		$this->client->loginUser($this->user);

		$user = $this->userRepository->findOneBy(["id" => self::USER_ID]);

		$token = new UsernamePasswordToken($this->user, 'main', ['memory']);

		$decisionManager = $this->client->getContainer()->get('security.access.decision_manager');

		$decision = $decisionManager->decide($token, $this->user->getRoles(), $user);

		$this->assertFalse($decision);

		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', [
			'id' => self::USER_ID
		]));

		$this->assertResponseRedirects($this->urlGenerator->generate('home_page'), 302);
		$this->client->followRedirect();
	}

	public function testEditUserRoleAdminWithoutPassword()
	{
		$this->client->loginUser($this->admin);

		$user = $this->userRepository->findOneBy(["id" => self::USER_ID]);

		$token = new UsernamePasswordToken($this->admin, 'main', ['memory']);

		$decisionManager = $this->client->getContainer()->get('security.access.decision_manager');

		$decision = $decisionManager->decide($token, $this->admin->getRoles(), $user);

		$this->assertTrue($decision);

		$crawler =  $this->client->request(
			Request::METHOD_GET, $this->urlGenerator->generate(
			'user_edit',
			[
				'id' => $user->getId()
			]));

		$this->assertResponseIsSuccessful();
		$this->assertSelectorExists('form');

		$form = $crawler->selectButton("Modifier")->form();

		//Add this "user[username]" in $form[] because "user[username]" is the name in the rendered field form name
		$form->setValues([
							 "user[username]" => ($user->getUsername() === "phpunit") ? "phpunit3" : "phpunit",
							 "user[email]" => $user->getEmail() === "phpunit@gmail.com" ? "phpunit3@gmail.com" : "phpunit@gmail.com",
							 "user[roles]" => self::USER_ROLES[0]
						 ]);

		$this->client->submit($form);
		$this->assertResponseRedirects($this->urlGenerator->generate('user_list'));
		$this->client->followRedirect();
	}

	public function testEditUserRoleAdminWithPassword()
	{
		$this->client->loginUser($this->admin);

		$user = $this->userRepository->findOneBy(["id" => self::USER_ID]);

		$token = new UsernamePasswordToken($this->admin, 'main', ['memory']);

		$decisionManager = $this->client->getContainer()->get('security.access.decision_manager');

		$decision = $this->decisionManager->decide($token, $this->admin->getRoles(), $user);

		$this->assertTrue($decision);

		$crawler =  $this->client->request(
			Request::METHOD_GET, $this->urlGenerator->generate(
			'user_edit',
			[
				'id' => self::USER_ID
			]));

		$this->assertResponseIsSuccessful();
		$this->assertSelectorExists('form');

		$form = $crawler->selectButton("Modifier")->form();

		//Add this "user[username]" in $form[] because "user[username]" is the name in the rendered field form name
		$form->setValues([
							 "user[username]" => ($user->getUsername() === "phpunit") ? "phpunit3" : "phpunit",
							 "user[email]" => ($user->getEmail() === "phpunit@gmail.com") ? "phpunit3@gmail.com" : "phpunit@gmail.com",
							 "user[password][first]" => "password",
							 "user[password][second]" => "password",
							 "user[roles]" => self::USER_ROLES[0]
						 ]);

		$this->client->submit($form);
		$this->assertResponseRedirects($this->urlGenerator->generate('user_list'));
		$this->client->followRedirect();
	}


}