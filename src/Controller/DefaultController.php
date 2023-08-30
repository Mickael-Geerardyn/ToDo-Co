<?php

namespace App\Controller;

	use Symfony\Component\Routing\Annotation\Route;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\Response;

	class DefaultController extends AbstractController
	{
		/**
		 * @Route("/home", name="homepage")
		 */
		public function getHomePage(): Response
		{
			return $this->render('default/index.html.twig');
		}
	}
