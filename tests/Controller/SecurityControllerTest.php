<?php

namespace Tests\Controller;
use App\Controller\SecurityController;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SecurityControllerTest extends WebTestCase
{
	private $client;
	private $user;
	private $userRepository;
	private $urlGenerator;

	public function setUp(): void
	{
		$this->client = static::createClient();
		$this->userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
		$this->user = $this->userRepository->findOneBy(['email' => 'phpunit3@gmail.com']);
		$this->urlGenerator = $this->client->getContainer()->get('router.default');
	}

	public function testLogout(): void
	{
		$this->client->loginUser($this->user);

		// If user is logged in, he can access to this page
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', ['id' => $this->user->getId()]));
		$this->assertResponseIsSuccessful();

		// Logout
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('app_logout'));

		// If user is logged out, he can't access to this page
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('user_edit', ['id' => $this->user->getId()]));
		$this->assertResponseRedirects($this->urlGenerator->generate('app_login'), 302);
		$this->client->followRedirect();
		$this->assertSelectorTextContains('div.alert.alert-danger', 'Vous devez être connecté pour modifier un compte.');
	}

	public function testLogoutMethodDirectly()
	{
		$controller = new SecurityController();
		$this->expectException(\LogicException::class);
		$controller->logout();
	}
}