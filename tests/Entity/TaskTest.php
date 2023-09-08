<?php

namespace Tests\Entity;

use App\Entity\User;
use App\Repository\TaskRepository;
use DateTime;
use PHPUnit\Framework\TestCase;
use App\Entity\Task;

class TaskTest extends TestCase
{
	public function testGetId()
	{
		$task = new Task();
		$this->assertNull($task->getId());
	}

	public function testGetSetTitle()
	{
		$task = new Task();
		$task->setTitle('Title test');
		$this->assertEquals('Title test', $task->getTitle());
	}

	public function testGetSetContent()
	{
		$task = new Task();
		$task->setContent('Content test');
		$this->assertEquals('Content test', $task->getContent());
	}

	public function testGetSetCreatedAt()
	{
		$task = new Task();
		$task->setCreatedAt(new DateTime());
		$this->assertInstanceOf(DateTime::class, $task->getCreatedAt());
	}

	public function testGetSetIsDone()
	{
		$task = new Task();
		$task->setIsDone(true);
		$this->assertTrue($task->isDone());
	}

	public function testGetSetAuthor()
	{
		$task = new Task();
		$user = new User();
		$task->setUser($user);
		$this->assertEquals($user, $task->getUser());
	}

}