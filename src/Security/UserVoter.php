<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
	const ROLE_ADMIN = "ROLE_ADMIN";

	protected function supports(string $attribute, $subject): bool
	{
		if ($attribute !== self::ROLE_ADMIN) {
			return false;
		}

		if (!$subject instanceof User) {
			return false;
		}

		return true;
	}

	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
	{
		$authenticatedUser = $token->getUser();

		if (!$authenticatedUser instanceof User) {

			return false;
		}

		/** @var User $user */
		$userObjectToEdit = $subject;

		if ($attribute !== self::ROLE_ADMIN) {

			throw new \LogicException('This code should not be reached!');
		}

		return $this->canEditUser($userObjectToEdit, $authenticatedUser);
	}

	public function canEditUser(User $authenticatedUser): bool
	{
		if(implode($authenticatedUser->getRoles()) !== self::ROLE_ADMIN)
		{
			return false;
		}

		return true;
	}
}