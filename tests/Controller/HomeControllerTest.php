<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeControllerTest extends WebTestCase
{
	private KernelBrowser|null $client = null;
	private UrlGeneratorInterface $urlGenerator;
	public function setUp() : void

	{
		$this->client = static::createClient();
		$this->urlGenerator = $this->client->getContainer()->get('router.default');
	}

	public function testGetHomePageIsUp()
    {
		$this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('home_page'));
		$this->assertResponseIsSuccessful();
    }
}
