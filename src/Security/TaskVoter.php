<?php

namespace App\Security;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
	const ROLE_ADMIN = "ROLE_ADMIN";
	const ROLE_USER = "ROLE_USER";
	protected function supports(string $attribute, $subject): bool
	{

		if (!in_array($attribute, [self::ROLE_ADMIN, self::ROLE_USER])) {
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

		/** @var $role */
		$authenticatedUserRole = implode($user->getRoles());

		/** @var Task $task */
		$task = $subject;


		return match ($authenticatedUserRole) {
			self::ROLE_ADMIN => $this->canDeleteOwnOrAnonymousTask($task, $user),
			self::ROLE_USER => $this->canDeleteOnlyByOwner($task, $user),
			default => false,
		};
	}

	public function canDeleteOwnOrAnonymousTask(Task $task, User $user): bool
	{
		if(empty($task->getUser()))
		{
			return true;
		}

		if($this->canDeleteOnlyByOwner($task, $user))
		{
			return true;
		}

		return false;
	}

	public function canDeleteOnlyByOwner(Task $task, User $user): bool
	{

		if($task->getUser()->getId() !== $user->getId())
		{
			return false;
		}

		return true;
	}
}