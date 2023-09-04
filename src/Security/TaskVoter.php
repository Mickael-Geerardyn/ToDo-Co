<?php

namespace App\Security;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
	const TASK_ANONYMOUS_DELETE_ROLE = "ROLE_ADMIN";
	const TASK_DELETE_MIN_ROLE = "ROLE_USER";
	protected function supports(string $attribute, $subject): bool
	{

		if (!in_array($attribute, [self::TASK_ANONYMOUS_DELETE_ROLE, self::TASK_DELETE_MIN_ROLE])) {
			return false;
		}

		if (!$subject instanceof Task) {
			return false;
		}

		return true;
	}

	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
	{
		$user = $token->getUser();

		if (!$user instanceof User) {

			return false;
		}

		/** @var Task $task */
		$task = $subject;

		switch ($attribute) {
			case self::TASK_ANONYMOUS_DELETE_ROLE:
				return $this->canDeleteAnonymousTask($task, $user);
			case self::TASK_DELETE_MIN_ROLE:
				return $this->canDeleteOnlyByOwner($task, $user);
		}

		throw new \LogicException('This code should not be reached!');
	}

	public function canDeleteAnonymousTask(Task $task, User $user): bool
	{
		if(implode($user->getRoles()) != self::TASK_ANONYMOUS_DELETE_ROLE)
		{
			return false;
		}

		return true;
	}

	public function canDeleteOnlyByOwner(Task $task, User $user): bool
	{
		if($task->getUser() !== $user)
		{
			return false;
		}

		return true;
	}
}