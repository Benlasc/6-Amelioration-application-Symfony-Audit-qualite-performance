<?php

namespace App\Tests\Controller;

use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use NeedLogin;

    /**
     * @var KernelBrowser $client
     */
    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * Get admin from the database test, authenticate him and execute the request
     * @param string $method
     * @param string $url
     * 
     * @return Crawler
     */
    public function AdminRequest(string $method = 'GET', string $url = '/'): Crawler
    {
        $users = $this->databaseTool->loadAliceFixture([
            __DIR__ . '/fixtures/UserTestFixtures.yaml',
        ]);
        
        $adminUser = $users['user_admin'];
        $this->login($this->client, $adminUser);
        return $this->client->request($method, $url);
    }

    // Users page access

    public function testRedirectToLoginIfNotAuthenticated(): void
    {
        $crawler = $this->client->request('GET', '/users');
        $this->assertResponseRedirects('/login');
    }

    public function testUnauthorizedAccessForUser(): void
    {
        $users = $this->databaseTool->loadAliceFixture([
            __DIR__ . '/fixtures/UserTestFixtures.yaml',
        ]);
        $user = $users['user_user'];
        $this->login($this->client, $user);
        $crawler = $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAuthorizedAccessForAdmin(): void
    {
        $crawler = $this->AdminRequest('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    // === User creation ===

    public function testSuccessfulUserCreation(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'User3',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass',
            'user[email]' => 'user3@domain.fr',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertSelectorTextContains('tbody', 'User3');
    }

    // Email already used
    public function testfailedUserCreation1(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'User4',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass',
            'user[email]' => 'user1@domain.fr',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('.form-group.has-error', 'Ce mail est déjà pris');
    }

    // The passwords are different
    public function testfailedUserCreation2(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'User4',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass2',
            'user[email]' => 'user4@domain.fr',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('.form-group.has-error', 'Les deux mots de passe doivent correspondre.');
    }

    // Missing data (username and email)
    public function testfailedUserCreation3(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => '',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass',
            'user[email]' => '',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(2, $crawler->filter('.has-error')->count());
    }

    // === User update ===

    // Access to the user update page
    public function testUpdateAccess(): void
    {
        $crawler = $this->AdminRequest('GET', '/users');

        $link = $crawler->selectLink('Edit')->link();
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier');
    }

    // Successful upgrade
    public function testSuccessfulUserUpdate(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'User5',
            'user[roles]' => 'ROLE_ADMIN',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass',
            'user[email]' => 'user5@domain.fr',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/users');
        $crawler = $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
        $this->assertSelectorTextContains('tbody', 'User5');
        $this->assertSelectorTextContains('tbody', 'user5@domain.fr');
        $this->assertSame(2, $crawler->filter('td:contains("ROLE_ADMIN")')->count());
    }

    // Missing data (username and email)
    public function testfailedUserUpdate(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => '',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass',
            'user[email]' => '',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertEquals(2, $crawler->filter('.has-error')->count());
    }

    // The passwords are different
    public function testfailedUserUpdate2(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/1/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'User4',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass2',
            'user[email]' => 'user4@domain.fr',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('.form-group.has-error', 'Les deux mots de passe doivent correspondre.');
    }

    // Email already used
    public function testfailedUserUpdate3(): void
    {
        $crawler = $this->AdminRequest('GET', '/users/3/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'User4',
            'user[roles]' => 'ROLE_USER',
            'user[password][first]' => 'pass',
            'user[password][second]' => 'pass',
            'user[email]' => 'admin@domain.fr',
        ]);

        $this->client->submit($form);
        $this->assertSelectorTextContains('.form-group.has-error', 'Ce mail est déjà pris');
    }

    // === User delete ===

    public function testSuccessfulUserDelete(): void
    {      
        $crawler = $this->AdminRequest('GET', 'users');

        $csrfToken = $crawler->filter('input')->attr('value');

        //$csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete1');
      
        $this->client->request('POST', '/users/3/delete', [
            '_token' => $csrfToken
        ]);

        $this->assertResponseRedirects('/users');
        $crawler = $this->client->followRedirect();

        $this->assertSame(0, $crawler->filter('table:contains("user1@domain.fr")')->count());
    }
}