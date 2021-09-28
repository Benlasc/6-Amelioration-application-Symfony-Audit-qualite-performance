<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SecurityControllerTest extends WebTestCase
{
    const BASE_HOST = 'http://localhost';

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var KernelBrowser */
    private $client = null;

    //se lance avant chaque test
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
            '_username' => 'john@doe.fr',
            '_password' => 'fakepassword',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects(self::BASE_HOST.'/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccesfullLogin()
    {
        $this->databaseTool->loadAliceFixture([
            __DIR__ . './users.yaml',
        ]);
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'john@doe.fr',
            'password' => '000000',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/auth');
    }
}
