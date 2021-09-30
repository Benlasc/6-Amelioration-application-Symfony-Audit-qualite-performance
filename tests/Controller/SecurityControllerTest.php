<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SecurityControllerTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var KernelBrowser */
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testDisplayLogin()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', "Nom d'utilisateur :");
        $this->assertSelectorTextContains('form', "Mot de passe :");
        $this->assertSelectorNotExists('.alert.alert-success');
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testLoginWithBadCredentials()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'john@doe.fr',
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
            __DIR__ . '/UserTestFixtures.yaml',
        ]);
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'User1',
            'password' => '121212',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');
    }
}
