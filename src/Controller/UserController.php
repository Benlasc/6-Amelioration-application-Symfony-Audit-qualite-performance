<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $entityManager;

    private $encoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $encoder)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function list(UserRepository $userRepository): response
    {
        return $this->render('user/list.html.twig', ['users' => $userRepository->findAll()]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function create(Request $request): response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {;
            $user->setPassword($this->encoder->hashPassword($user, $user->getPassword()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     */
    public function edit(User $user, Request $request): response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->encoder->hashPassword($user, $user->getPassword()));
            $role = $request->request->all()['user']['roles'];
            $user->setRoles((array) $role );
            $this->entityManager->flush();
            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * @Route("/users/{id}/delete", name="user_delete")
     */
    public function delete(User $user, Request $request): response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {

            // The tasks of deleted users are linked to the anonymous user
            $tasks = $user->getTasks();
            foreach ($tasks as $task) {
                $anonymousUser = $this->entityManager->getRepository(User::class)->findByUsername('Utilisateur anonyme')[0];
                /**
                 * @var Task $task
                 */
                $task->setUser($anonymousUser);
            }

            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $this->addFlash('success', "L'utilisateur a bien été supprimé");
        }

        return $this->redirectToRoute('user_list', [], Response::HTTP_SEE_OTHER);
    }
}
