<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlsControllerTest extends WebTestCase
{
    /** @test */
    public function homepage_should_display_url_shortener_form(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('URL Shortener');
        $this->assertSelectorTextContains('h1', 'The best URL shortener out there !');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[id="form_original"]');
        $this->assertSelectorExists('input[name="form[original]"]');
        $this->assertSelectorExists('input[placeholder="Enter the URL to shorten here"]');
    }

    /** @test */
    public function form_should_work_with_valid_data()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        // Avec un bouton de soumission (voir « create.html.twig ») :
        // $client->submitForm('Shorten URL', [
        //     'form[original]' => 'https://symfony.com'
        // ]);

        // Sans bouton de soumission :
        $form = $crawler->filter('form')->form();
        $client->submit($form, [
            'form[original]' => 'https://symfony.com'
        ]);

        $this->assertResponseRedirects();
    }
}
