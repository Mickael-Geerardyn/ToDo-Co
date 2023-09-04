<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
	public function __construct(private readonly EntityManagerInterface $entityManager, private readonly RequestStack $requestStack, private readonly UserPasswordHasherInterface $passwordHasher)
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

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }


	#[Route("/users/{id}/edit", name: "user_edit", methods: ["GET", "PATCH", "POST"])]
    public function editUser(User $user): Response
    {
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

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }
}
