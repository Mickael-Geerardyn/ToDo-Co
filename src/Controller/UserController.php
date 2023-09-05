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

	#[Route("/users", name: "user_list", methods: ["GET"])]
    public function getUsers(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

	#[Route("users/create", name: "user_create", methods: ["GET", "POST"])]
    public function createUser(): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

			$user->setPassword(
				$this->passwordHasher->hashPassword(
					$user,
					$form->get('plainPassword')->getData()
				),
			);

			$selectedRole[] = $form->get("roles")->getData();
			$user->setRoles($selectedRole);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list', status: self::HTTP_STATUS_CREATED);
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }



	#[Route("/users/{id}/edit", name: "user_edit", methods: ["GET", "PATCH", "POST"])]
    public function editUser(User $user): Response
    {
		//Add this to check ROLE in user object because only ADMIN_ROLE.
		// security.yaml is not enough because if user enters the url manually, he can access to the editing page
		// UserVoter is called by denyAccessUnlessGranted method
		if($this->denyAccessUnlessGranted($this->userVoter::ROLE_ADMIN, $user) === false)
		{
			$this->addFlash('error','Vous ne pouvez pas éditer les utilisateurs');

			return $this->redirectToRoute('home_page', status: self::HTTP_STATUS_FORBIDDEN);
		}

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

			$user->setPassword(
				$this->passwordHasher->hashPassword(
					$user,
					$form->get('plainPassword')->getData()
				),
			);

            $this->entityManager->persist($user);
			$this->entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list', status: self::HTTP_STATUS_OK);
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
