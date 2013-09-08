<?php

namespace Cerad\Bundle\TournBundle\Tests\Controller\Tourn;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/test');
        
        //echo $crawler->html();
        
        $this->assertTrue($crawler->filter('html:contains("TournTestIndex Page")')->count() > 0);
    }
}
