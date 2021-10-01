<?php

namespace App\Tests\Utils;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

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
     * Get admin or normal user from the database test, authenticate him and execute the request
     * @param string $role (user or admin)
     * @param string $method (GET or POST)
     * @param string $url
     * @param array|null $post
     *
     * @return Crawler
     */
    public function UserRequest(string $method, string $url, ?array $post = null, string $role = 'user', ): Crawler
    {
        // $this->database = $this->databaseTool->loadAliceFixture([
        //     __DIR__ . '/fixtures/DataTestFixtures.yaml',
        // ]);

        $this->database = ($this->database) ? 
            $this->database : 
            $this->databaseTool->loadAliceFixture([__DIR__ . '/fixtures/DataTestFixtures.yaml']) ;

        $user = ($role == 'user') ? $this->database['user_user'] : $this->database['user_admin'] ;

        $this->login($this->client, $user);

        if ($post && $method == 'POST') {
            return $this->client->request($method, $url, $post);
        } else {
            return $this->client->request($method, $url);
        }
    }

    public function loadFixture()
    {
        $this->database = $this->databaseTool->loadAliceFixture([
            __DIR__ . '/fixtures/DataTestFixtures.yaml',
        ]);
    }
}
