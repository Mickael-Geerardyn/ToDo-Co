<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Security\TaskVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
	private const HTTP_STATUS_FORBIDDEN = 403;
    public function __construct(
      private readonly EntityManagerInterface $entityManager,
	  private readonly RequestStack $requestStack,
	  private readonly TaskRepository $taskRepository,
	  private readonly TaskVoter $taskVoter
    )
    {
    }

	/**
	 * @return Response
	 */
	#[Route(path: "/tasks", name: "task_list")]
    public function getTasks(): Response
	{
        return $this->render('task/list.html.twig', ['tasks' => $this->taskRepository->findAll()]);
    }

	/**
	 * @return Response|RedirectResponse
	 */
	#[Route(path: "/tasks/create", name: "task_create", methods: ["GET", "POST"])]
    public function createTasks(): Response|RedirectResponse
    {
        $task = new Task();
		$task->setUser($this->getUser());
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid() === true) {

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

	/**
	 * @param Task $task
	 *
	 * @return Response|RedirectResponse
	 */
	#[Route(path: "/tasks/{id}/edit", name: "task_edit", requirements: ["id" => "\d+"], methods: ["GET", "POST", "PATCH", "PUT"])]
    public function editTasks(Task $task): Response|RedirectResponse
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid() === true) {

            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

	/**
	 * @param Task $task
	 *
	 * @return RedirectResponse
	 */
	#[Route(path: "/tasks/{id}/toggle", name: "task_toggle", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function toggleTaskAction(Task $task): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $this->entityManager->flush();

		if($task->isDone() === true){
			$this->addFlash('success','La tâche '.$task->getTitle().' a bien été marquée comme terminée.');
		}elseif ($task->isDone() === false) {
			$this->addFlash('success','La tâche '.$task->getTitle().' a bien été marquée comme non terminée.');
		}


        return $this->redirectToRoute('task_list');
    }


	/**
	 * @param Task $task
	 *
	 * @return RedirectResponse
	 */
	#[Route(path: "/tasks/{id}/delete", name: "task_delete")]
    public function deleteTaskAction(Task $task): RedirectResponse
    {
		//Add this to check ROLE in user object. Both of USER and ADMIN role can delete a task but only own task for
		// USER and anonymous task for ADMIN
		// TaskVoter is called by denyAccessUnlessGranted method
		if($this->denyAccessUnlessGranted($this->taskVoter::ROLE_USER[0], $task) === false)
		{
			$this->addFlash('error','Vous ne pouvez pas supprimer cette tâche');

			return $this->redirectToRoute('task_list', status: self::HTTP_STATUS_FORBIDDEN);
		}

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
