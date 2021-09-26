<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $usersData = [
            0 => [
                'userName' => 'Utilisateur anonyme',
                'email' => 'anonyme@domain.com',
                'role' => ['ROLE_USER'],
                'password' => 123456,
                'tasks' => [
                    0 => [
                        "title" => "Titre 1",
                        "content" => "Contenu 1",
                        "isDone" => 0
                    ],
                    1 => [
                        "title" => "Titre 2",
                        "content" => "Contenu 2",
                        "isDone" => 0
                    ],
                    2 => [
                        "title" => "Titre 3",
                        "content" => "Contenu 3",
                        "isDone" => 0
                    ]
                ]
            ],
            1 => [
                'userName' => 'Admin',
                'email' => 'admin@domain.com',
                'role' => ['ROLE_ADMIN'],
                'password' => 'admin',
                'tasks' => []
            ],
            2 => [
                'userName' => 'User',
                'email' => 'user@domain.com',
                'role' => ['ROLE_USER'],
                'password' => 'user',
                'tasks' => [
                    0 => [
                        "title" => "Titre 4",
                        "content" => "Contenu 4",
                        "isDone" => 0
                    ],
                    1 => [
                        "title" => "Titre 5",
                        "content" => "Contenu 5",
                        "isDone" => 0
                    ],
                    2 => [
                        "title" => "Titre 6",
                        "content" => "Contenu 6",
                        "isDone" => 0
                    ],
                    3 => [
                        "title" => "Titre 7",
                        "content" => "Contenu 7",
                        "isDone" => 0
                    ],
                    4 => [
                        "title" => "Titre 8",
                        "content" => "Contenu 8",
                        "isDone" => 1
                    ],
                    5 => [
                        "title" => "Titre 9",
                        "content" => "Contenu 9",
                        "isDone" => 1
                    ]
                ]
            ]
        ];

        foreach ($usersData as $user) {
            $newUser = new User();
            $newUser->setUsername($user['userName']);
            $newUser->setEmail($user['email']);
            $newUser->setPassword($this->encoder->hashPassword($newUser, $user['password']));
            $newUser->setRoles($user['role']);

            foreach ($user['tasks'] as $taskInformations) {
                $newTask = new Task();
                $newTask->setTitle($taskInformations['title']);
                $newTask->setContent($taskInformations['content']);
                $newUser->addTask($newTask);
            }

            $manager->persist($newUser);
        }
        $manager->flush();
    }
}
