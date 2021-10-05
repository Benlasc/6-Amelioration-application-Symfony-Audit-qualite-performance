<?php

namespace App\Tests\Controller;

use App\Tests\Utils\CustomWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends CustomWebTestCase
{
    public function testDisplayLogin()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', "Nom d'utilisateur :");
        $this->assertSelectorTextContains('form', 'Mot de passe :');
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testLoginWithBadCredentials()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'fakemail@domain.fr',
            'password' => 'fakepassword',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccesfullLogin()
    {
        $this->databaseTool->loadAliceFixture([
            __DIR__.'/../Utils/fixtures/DataTestFixtures.yaml',
        ]);
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'User1',
            'password' => '121212',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');
    }

    public function testRedirectToHomepageIfAuthenticated()
    {
        $this->UserRequest('GET', '/login');

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
    }
}
