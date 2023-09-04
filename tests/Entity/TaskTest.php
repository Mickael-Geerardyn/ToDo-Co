<?php

namespace Tests\Entity;

use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
	public function __construct(?string $name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
	}
}