<?php

namespace App\Tests\Controller;

use App\Entity\Url;
use App\Util\Str;
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

    /** @test */
    public function shortened_version_should_redirect_to_original_url()
    {
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();

        $original = 'https://symfony.com';
        $shortened = Str::random(6);

        $url = (new Url)
            ->setOriginal($original)
            ->setShortened($shortened)
        ;

        $em->persist($url);
        $em->flush();

        $client->request('GET', '/' . $shortened);

        $this->assertResponseRedirects($original);
    }

    /** @test */
    public function preview_shortened_version_should_works()
    {
        $client = static::createClient();

        $em = self::$container->get('doctrine')->getManager();

        $original = 'https://symfony.com';
        $shortened = Str::random(6);

        $url = (new Url)
            ->setOriginal($original)
            ->setShortened($shortened)
        ;

        $em->persist($url);
        $em->flush();

        $crawler = $client->request('GET', sprintf('/%s/preview', $shortened));

        // dd($client->getResponse());
        $this->assertSelectorTextContains('h1', 'Yay! Here is your shortened URL:');
        $this->assertSelectorTextContains('h1 > a', sprintf('http://localhost/%s', $shortened));


        // ‼ ATTENTION : faux positif ‼ (avec « path() », voir « preview.html.twig »)
        $this->assertSelectorTextContains('h1 > a[href]', sprintf('http://localhost/%s', $shortened));

        // Bonne solution :

        // avec « path() » (voir « preview.html.twig »)
        // $this->assertSame(sprintf('/%s', $shortened), $crawler->filter('h1 > a')->attr('href'));

        // avec « url() » (voir « preview.html.twig ») ‼ 
        $this->assertSame(sprintf('http://localhost/%s', $shortened), $crawler->filter('h1 > a')->attr('href'));


        // dd($crawler->filter('a')->eq(1)->text());
        $this->assertSame('Go back home', $crawler->filter('a')->eq(1)->text());

        $client->clickLink('Go back home');
        $this->assertRouteSame('app_home');
    }
}
