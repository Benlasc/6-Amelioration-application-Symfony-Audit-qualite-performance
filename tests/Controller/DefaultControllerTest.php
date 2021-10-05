<?php

namespace App\Tests\Controller;

use App\Tests\Utils\CustomWebTestCase;

class DefaultControllerTest extends CustomWebTestCase
{
    public function testIndex()
    {
        $this->UserRequest('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List');
    }
}
