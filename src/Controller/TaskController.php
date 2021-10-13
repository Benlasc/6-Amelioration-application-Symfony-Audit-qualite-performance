<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/tasks", name="task_list")
     */
    public function list(Request $request): Response
    {
        /** @var User $user  */
        $user = $this->getUser();

        if ($user->getRoles() === ['ROLE_ADMIN']) {
            $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        } else {
            $done = $request->query->get('done') ?? null;
            if (null !== $done) {
                if ('true' === $done) {
                    $tasks = $user->getDoneTasks();
                } elseif ('false' === $done) {
                    $tasks = $user->getNotDoneTasks();
                } else {
                    $tasks = $user->getTasks();
                }
            } else {
                $tasks = $user->getTasks();
            }
        }

        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function create(Request $request): response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        /** @var User $user */
        $user = $this->getUser();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($user);
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit"): response
     * @return Response
     */
    public function edit(Task $task, Request $request)
    {
        if (!$this->isGranted('task_edit', $task)) {
            $this->addFlash('error', "Vous n'êtes pas l'auteur de cette tache.");

            return $this->redirectToRoute('task_list');
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

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

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTask(Task $task): response
    {
        if (!$this->isGranted('task_edit', $task)) {
            $this->addFlash('error', "Vous n'êtes pas l'auteur de cette tache.");

            return $this->redirectToRoute('task_list');
        }

        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        if ($task->isDone()) {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        } else {
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme non terminée.', $task->getTitle()));
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTask(Task $task, Request $request): response
    {
        
        /** @var string|null $token */
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $token)) {
            if (!$this->isGranted('task_delete', $task)) {
                $this->addFlash('error', "Vous n'êtes pas l'auteur de cette tache.");

                return $this->redirectToRoute('task_list');
            }

            $this->entityManager->remove($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été supprimée.');
        }

        return $this->redirectToRoute('task_list');
    }
}
