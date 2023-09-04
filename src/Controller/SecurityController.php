<?php

namespace App\Controller;


use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
	#[Route(path: "/login", name: "app_login", methods: ["GET", "POST"])]
    public function loginUser(AuthenticationUtils $authenticationUtils): Response
    {
		 if ($this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
		     return $this->redirectToRoute('home_page');
		 }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error'         => $error]);
    }


	#[Route(path: "/logout", name: "app_logout")]
    public function logout(): void
    {
    }
}
