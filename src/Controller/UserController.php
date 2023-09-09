<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
	private const HTTP_STATUS_OK = 200;
	private const HTTP_STATUS_CREATED = 201;
	private const HTTP_STATUS_FORBIDDEN = 403;
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly RequestStack $requestStack,
		private readonly UserPasswordHasherInterface $passwordHasher,
		private readonly UserVoter $userVoter
	)
 {
 }

	/**
	 * @param UserRepository $userRepository
	 *
	 * @return Response
	 */
	#[Route("/users", name: "user_list", methods: ["GET"])]
    public function getUsers(UserRepository $userRepository): Response
    {
		if($this->denyAccessUnlessGranted(implode($this->getUser()->getRoles()), $this->getUser()) === false) {

			$this->addFlash('error',"L'accès à cette page n'est pas autorisé");

			return $this->redirectToRoute('app_login');
		}

        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

	/**
	 * @return Response
	 */
	#[Route("users/create", name: "user_create", methods: ["GET", "POST"])]
    public function createUser(): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
			'validation_groups' => ['Registration'],
			'is_create' => true
		]);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid() === true) {

			$user->setPassword(
				$this->passwordHasher->hashPassword(
					$user,
					$form->get('password')->getData()
				),
			);

			$user->setRoles($form->get("roles")->getData());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list', status: self::HTTP_STATUS_CREATED);
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }


	/**
	 * @param User $user
	 *
	 * @return Response
	 */
	#[Route("/users/{id}/edit", name: "user_edit", methods: ["GET", "PATCH", "POST"])]
    public function editUser(User $user): Response
    {
		//Add this to check ROLE in user object because only ADMIN_ROLE.
		// security.yaml is not enough because if user enters the url manually, he can access to the editing page
		// UserVoter is called by denyAccessUnlessGranted method
		if($this->denyAccessUnlessGranted($this->userVoter::ROLE_ADMIN, $user) === false)
		{
			$this->addFlash('error','Vous ne pouvez pas éditer les utilisateurs');

			return $this->redirectToRoute('home_page');
		}

        $form = $this->createForm(UserType::class, $user, [
			'is_create' => false
		]);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid() === true) {

			if($form->get('password')->getData())
			{
				$user->setPassword(
					$this->passwordHasher->hashPassword(
						$user,
						$form->get('password')->getData()
					),
				);
			}

			if($form->get("roles")->getData() !== implode($user->getRoles()))
			{
				$user->setRoles($form->get("roles")->getData());
			}

            $this->entityManager->persist($user);
			$this->entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
