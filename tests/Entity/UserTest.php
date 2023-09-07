<?php

namespace Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	public function testGetId()
	{
		$user = new User();
		$this->assertNull($user->getId());
	}

	public function testGetSetEmail()
	{
		$user = new User();
		$user->setEmail('phpunit-test@gmail.com');
		$this->assertEquals('phpunit-test@gmail.com', $user->getEmail());
	}

	public function testUserIdentifier()
	{
		$user = new User();
		$user->setEmail('phpunit-test@gmail.com');
		$this->assertEquals('phpunit-test@gmail.com', $user->getUserIdentifier());
	}

	public function testGetSetUsername()
	{
		$user = new User();
		$user->setUsername('Name-test');
		$this->assertEquals('Name-test', $user->getUsername());
	}

	public function testGetSetPassword()
	{
		$user = new User();
		$user->setPassword('password');
		$this->assertEquals('password', $user->getPassword());
	}

	public function testGetSetRoles()
	{
		$user = new User();
		$user->setRoles('ROLE_USER');
		$this->assertEquals(['ROLE_USER'], $user->getRoles());
	}

	public function testEraseCredentials()
	{
		$user = new User();
		$this->assertNull($user->eraseCredentials());
	}

	public function testGetTasks()
	{
		$user = new User();
		$this->assertCount(0, $user->getTasks());
	}

	public function testAddTask()
	{
		$user = new User();
		$task = $this->getMockBuilder(Task::class)
					 ->disableOriginalConstructor()
					 ->getMock();
		$task->method('getAuthor')->willReturn($user);

		$user->addTask($task);

		$this->assertCount(1, $user->getTasks());
		$this->assertSame($task, $user->getTasks()[0]);
	}

	public function testRemoveTask()
	{
		$user = new User();
		$task = $this->getMockBuilder(Task::class)
					 ->disableOriginalConstructor()
					 ->getMock();
		$task->method('getAuthor')->willReturn($user);

		$user->addTask($task);
		$this->assertCount(1, $user->getTasks());

		$user->removeTask($task);
		$this->assertCount(0, $user->getTasks());
	}
}