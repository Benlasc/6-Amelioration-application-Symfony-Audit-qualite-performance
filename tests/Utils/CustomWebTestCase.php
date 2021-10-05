<?php

namespace App\Tests\Utils;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class CustomWebTestCase extends WebTestCase
{
    use NeedLogin;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    protected $database = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * Get admin or normal user from the database test, authenticate him and execute the request.
     *
     * @param string $role   (user or admin)
     * @param string $method (GET or POST)
     */
    public function UserRequest(string $method, string $url, ?array $post = null, string $role = 'user', ): Crawler
    {
        $this->database = ($this->database) ?
            $this->database :
            $this->databaseTool->loadAliceFixture([__DIR__.'/fixtures/DataTestFixtures.yaml']);

        $user = ('user' == $role) ? $this->database['user_user'] : $this->database['user_admin'];

        $this->login($this->client, $user);

        if ($post && 'POST' == $method) {
            return $this->client->request($method, $url, $post);
        }

        return $this->client->request($method, $url);
    }

    public function loadFixture()
    {
        $this->database = $this->databaseTool->loadAliceFixture([
            __DIR__.'/fixtures/DataTestFixtures.yaml',
        ]);
    }
}
