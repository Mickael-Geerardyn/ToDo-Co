<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HomeControllerTest extends WebTestCase
{
	private KernelBrowser|null $client = null;
	public function setUp() : void

	{
		$this->client = static::createClient();
	}

	public function testGetHomePageIsUp()
    {
		$urlGenerator = $this->client->getContainer()->get('router.default');

		$this->client->request(Request::METHOD_GET, $urlGenerator->generate('home_page'));
		$this->assertEquals(200, $this->client->getResponse());
    }
}
