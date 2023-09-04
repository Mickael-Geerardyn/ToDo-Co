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
    public function __construct(
      private readonly EntityManagerInterface $entityManager,
	  private readonly RequestStack $requestStack,
	  private readonly TaskRepository $taskRepository
    )
    {
    }

	#[Route(path: "/tasks", name: "task_list")]
    public function showTasks(): Response
	{
        return $this->render('task/list.html.twig', ['tasks' => $this->taskRepository->findAll()]);
    }

	#[Route(path: "/tasks/create", name: "task_create")]
    public function createTasks(): Response|RedirectResponse
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

			$this->getUser() ? $task->setUser($this->getUser()) : null;

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

	#[Route(path: "/tasks/{id}/edit", name: "task_edit", requirements: ["id" => "\d+"], methods: ["GET", "POST"])]
    public function editTasks(Task $task): Response|RedirectResponse
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($this->requestStack->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

	#[Route(path: "/tasks/{id}/toggle", name: "task_toggle", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function toggleTaskAction(Task $task): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $this->entityManager->flush();

        $this->addFlash('success','La tâche ' . $task->getTitle() . ' a bien été marquée comme faite.');

        return $this->redirectToRoute('task_list');
    }

	#[Route(path: "/tasks/{id}/delete", name: "task_delete", methods: ["DELETE"])]
    public function deleteTaskAction(Task $task): RedirectResponse
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
