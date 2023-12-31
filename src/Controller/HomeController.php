<?php

namespace App\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Routing\Annotation\Route;

	class HomeController extends AbstractController
	{
		/**
		 * @return Response
		 */
		#[Route(path: "/", name: "home_page")]
  		public function getHomePage(): Response
		{
			return $this->render('default/index.html.twig');
		}
	}
