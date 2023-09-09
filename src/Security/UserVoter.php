<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
	const USER_ROLES = ["ROLE_ADMIN", "ROLE_USER"];

	protected function supports(string $attribute, $subject): bool
	{
		if (in_array($attribute, self::USER_ROLES) === false) {
			return false;
		}

		if ($subject instanceof User === false) {
			return false;
		}

		return true;
	}

	protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
	{
		$authenticatedUser = $token->getUser();
		dump($authenticatedUser->getRoles());
		dump($authenticatedUser->getEmail());

		if ($authenticatedUser instanceof User === false) {


			return false;
		}

		/** @var User $user */
		$userObjectToEdit = $subject;

		if (in_array($attribute, self::USER_ROLES) === true) {

			return $this->canEditUser($authenticatedUser);
		}

		return false;
	}

	public function canEditUser(User $authenticatedUser): bool
	{

		if(implode($authenticatedUser->getRoles()) !== self::USER_ROLES[0])
		{
			return false;
		}

		return true;
	}
}