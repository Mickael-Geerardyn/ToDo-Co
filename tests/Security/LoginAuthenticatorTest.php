<?php

namespace Tests\Security;

use App\Security\LoginAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class LoginAuthenticatorTest extends WebTestCase
{
	private $urlGeneratorMock;
	private $requestMock;

	protected function setUp(): void
	{
		$this->urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
		$this->requestMock = new Request();
	}

	public function testGetLoginUrl()
	{
		$this->urlGeneratorMock
			->expects($this->once())
			->method('generate')
			->with(LoginAuthenticator::LOGIN_ROUTE)
			->willReturn('/expected/route');

		$authenticator = new LoginAuthenticator($this->urlGeneratorMock);

		// Using reflection to test private method
		$reflection = new \ReflectionClass($authenticator);
		$method = $reflection->getMethod('getLoginUrl');
		$method->setAccessible(true);

		$result = $method->invokeArgs($authenticator, [$this->requestMock]);

		$this->assertEquals('/expected/route', $result);
	}

	public function testAuthenticate()
	{
		$requestMock = $this->createMock(Request::class);
		$sessionMock = $this->createMock(Session::class);
		$parameterBagMock = $this->createMock(ParameterBag::class);

		$requestMock->expects($this->once())
					->method('getSession')
					->willReturn($sessionMock);

		$parameterBagMock->expects($this->exactly(3))
						 ->method('get')
						 ->willReturnMap([
											 ['email', '', 'test@test.com'],
											 ['password', '', 'password123'],
											 ['_csrf_token', '', 'some_token'],
										 ]);

		// Associate ParameterBag mock to request mock
		$requestMock->request = $parameterBagMock;

		$authenticator = new LoginAuthenticator($this->urlGeneratorMock);
		$passport = $authenticator->authenticate($requestMock);

		$this->assertInstanceOf(Passport::class, $passport);
	}

	public function testOnAuthenticationSuccess()
	{
		$requestMock = $this->createMock(Request::class);
		$sessionMock = $this->createMock(Session::class);
		$tokenMock = $this->createMock(TokenInterface::class);

		$requestMock->expects($this->once())
					->method('getSession')
					->willReturn($sessionMock);

		$this->urlGeneratorMock->expects($this->once())
							   ->method('generate')
							   ->with('app_default')
							   ->willReturn('/default');

		$authenticator = new LoginAuthenticator($this->urlGeneratorMock);
		$response = $authenticator->onAuthenticationSuccess($requestMock, $tokenMock, 'main');

		$this->assertEquals('/default', $response->getTargetUrl());
	}

	public function testOnAuthenticationSuccessWithTargetPath(): void
	{
		$requestMock = $this->createMock(Request::class);
		$sessionMock = $this->createMock(Session::class);
		$requestMock->method('getSession')->willReturn($sessionMock);

		// Configure the session mock to return a fake target path
		$firewallName = 'main';
		$sessionMock->method('get')->with($this->equalTo("_security.${firewallName}.target_path"))->willReturn('/fake-target-path');

		// Create a instance of the authenticator
		$authenticator = new LoginAuthenticator($this->urlGeneratorMock);

		$response = $authenticator->onAuthenticationSuccess($requestMock, $this->createMock(TokenInterface::class), $firewallName);

		// Check that the response is a redirect to the fake target path
		$this->assertEquals('/fake-target-path', $response->getTargetUrl());
	}

}
