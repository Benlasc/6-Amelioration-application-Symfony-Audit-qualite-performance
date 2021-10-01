<?php

namespace App\Tests\Controller;

use App\Tests\Utils\CustomWebTestCase;

class TaskControllerTest extends CustomWebTestCase
{
    public function testRedirectToLoginIfNotAuthenticated(): void
    {
        $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
    }

    public function testAuthorizedAccessForAdmin(): void
    {
        $this->UserRequest('GET', '/tasks', role:'admin', );
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Contenu de la première tâche');
        $this->assertSelectorTextContains('body', 'Contenu de la deuxième tâche');
        $this->assertSelectorTextContains('body', 'Contenu de la troisième tâche');
    }

    public function testAuthorizedAccessForUser(): void
    {
        $crawler = $this->UserRequest('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Contenu de la première tâche');
        $this->assertSelectorTextContains('body', 'Contenu de la deuxième tâche');
        $this->assertSelectorTextNotContains('body', 'Contenu de la troisième tâche');
    }

    public function testSeeDoneTaskForUser(): void
    {
        $crawler = $this->UserRequest('GET', '/tasks?done=true');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Contenu de la première tâche');
        $this->assertSelectorTextNotContains('body', 'Contenu de la deuxième tâche');
    }

    public function testSeeOngoingTaskForUser(): void
    {
        $crawler = $this->UserRequest('GET', '/tasks?done=false');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Contenu de la deuxième tâche');
        $this->assertSelectorTextNotContains('body', 'Contenu de la première tâche');
    }

    public function testUserDeleteTask(): void
    {
        $crawler = $this->UserRequest('GET', 'tasks');
        $csrfToken = $crawler->filter('input')->attr('value');
        $task = $this->database['task_1'];

        $this->client->request(
            'POST',
            '/tasks/'.$task->getId().'/delete',
            ['_token' => $csrfToken]
        );

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'La tâche a bien été supprimée.');
        $this->assertSelectorTextNotContains('body', 'Contenu de la première tâche');
    }

    public function testAdminDeleteTask(): void
    {
        $crawler = $this->UserRequest('GET', 'tasks', null, 'admin');
        $csrfToken = $crawler->filter('input')->eq(1)->attr('value');
        $task = $this->database['task_1'];

        $this->client->request(
            'POST',
            '/tasks/'.$task->getId().'/delete',
            ['_token' => $csrfToken]
        );

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'La tâche a bien été supprimée.');
        $this->assertSelectorTextNotContains('body', 'Contenu de la première tâche');
    }

    public function testUnauthorizedTaskRemoval(): void
    {
        $this->loadFixture();
        $taskId = $this->database['task_3']->getId();
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete'.$taskId);

        $this->UserRequest(
            'POST',
            '/tasks/'.$taskId.'/delete',
            ['_token' => $csrfToken->getValue()]
        );

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', "Vous n'êtes pas l'auteur de cette tache.");
    }

    public function testSetTaskDone(): void
    {
        $crawler = $this->UserRequest('GET', 'tasks');

        $form = $crawler->selectButton('Marquer comme faite')->form();

        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche Tâche 2 a bien été marquée comme faite.');
    }

    public function testSetTaskNotDone(): void
    {
        $crawler = $this->UserRequest('GET', 'tasks');

        $form = $crawler->selectButton('Marquer non terminée')->form();

        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-success', 'Superbe ! La tâche Tâche 1 a bien été marquée comme non terminée.');
    }

    public function testUnauthorizedSetTaskDone(): void
    {
        $crawler = $this->UserRequest('POST', 'tasks/1/toggle');

        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-danger', "Oops ! Vous n'êtes pas l'auteur de cette tache.");
    }

    public function testCreateNewTask(): void
    {
        $crawler = $this->UserRequest('GET', 'tasks');
        $link = $crawler->selectLink('Créer une tâche')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->selectButton('Ajouter')->form([
            "task[title]" => "Tâche 4",
            "task[content]" => "Contenu de la quatrième tâche",
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', "Tâche 4");
        $this->assertSelectorTextContains('body', "Contenu de la quatrième tâche");
    }

    public function testUpdateTask(): void
    {
        $crawler = $this->UserRequest('GET', 'tasks');
        $link = $crawler->selectLink('Tâche 1')->link();
        $crawler = $this->client->click($link);
        $form = $crawler->selectButton('Modifier')->form([
            "task[title]" => "Tâche 1 modifiée",
            "task[content]" => "Contenu de la première tâche modifié",
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', "Tâche 1 modifiée");
        $this->assertSelectorTextContains('body', "Contenu de la première tâche modifié");
    }

    public function testUnauthorizedUpdateTask(): void
    {
        $crawler = $this->UserRequest('POST', 'tasks/1/edit');

        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-danger', "Oops ! Vous n'êtes pas l'auteur de cette tache.");
    }

    public function testTaskWithoutUser(): void
    {
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete3');

        $this->UserRequest(
            'POST',
             'users/3/delete', 
             ['_token' => $csrfToken->getValue()],
             'admin'
        );

        $this->client->followRedirect();

        $crawler = $this->UserRequest('GET', 'tasks', null, 'admin');

        $this->assertSelectorTextNotContains('body', "User1");

        $this->assertSame(3, $crawler->filter('p:contains("Utilisateur anonyme (anonyme@domain.com)")')->count());
    }
}