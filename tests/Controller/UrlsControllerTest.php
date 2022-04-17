<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlsControllerTest extends WebTestCase
{
    public function testHomepageShouldDisplayUrlShortenerForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('URL Shortener');
        $this->assertSelectorTextContains('h1', 'The best URL shortener out there !');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[id="form_original"]');
        $this->assertSelectorExists('input[name="form[original]"]');
        $this->assertSelectorExists('input[placeholder="Enter the URL to shorten here"]');
    }
}
