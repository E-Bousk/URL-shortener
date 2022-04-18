<?php

namespace App\Tests\Controller;

use App\Entity\Url;
use Illuminate\Support\Str;
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
        $this->assertSelectorExists('input[id="url_form_original"]');
        $this->assertSelectorExists('input[name="url_form[original]"]');
        $this->assertSelectorExists('input[placeholder="Enter the URL to shorten here"]');
    }

    /** @test */
    public function create_should_shorten_url_if_that_doesnt_exists_yet()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $original = 'https://symfony.com';

        // Avec un bouton de soumission (voir « create.html.twig ») :
        // $client->submitForm('Shorten URL', [
        //     'url_form[original]' => $original
        // ]);

        // Sans bouton de soumission :
        $form = $crawler->filter('form')->form();
        $client->submit($form, [
            'url_form[original]' => $original
        ]);

        $em = static::$container->get('doctrine')->getManager();

        $urlRepository = $em->getRepository(Url::class);

        $url = $urlRepository->findOneBy(compact('original'));
        // dd($url);

        $this->assertResponseRedirects(sprintf('/%s/preview', $url->getShortened()));
    }

    /** @test */
    public function create_should_shorten_url_once()
    {
        $client = static::createClient();
        
        $em = static::$container->get('doctrine')->getManager();
        
        $original = 'https://symfony.com';
        $url = (new Url)
            ->setOriginal($original)
            ->setShortened('test')
        ;
        $em->persist($url);
        $em->flush();
        
        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('form')->form();
        $client->submit($form, [
            'url_form[original]' => $original
        ]);

        $this->assertResponseRedirects('/test/preview');
    
        $urlRepository = $em->getRepository(Url::class);

        $this->assertCount(1, $urlRepository->findAll());
    }

    /** @test */
    public function shortened_version_should_redirect_to_original_url_if_shortened_exists()
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
    public function show_should_return_404_response_if_shortened_doesnt_exists()
    {
        $client = static::createClient();

        $client->request('GET', '/shouldnt_works');

        $this->assertResponseStatusCodeSame(404);
    }

    /** @test */
    public function preview_shortened_version_should_works_if_shortened_exists()
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

    /** @test */
    public function preview_should_return_404_response_if_shortened_doesnt_exists()
    {
        $client = static::createClient();

        $client->request('GET', '/shouldnt_works/preview');

        $this->assertResponseStatusCodeSame(404);
    }

    /** @test */
    public function original_should_not_be_blank()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('form')->form();
        $client->submit($form, [
            'url_form[original]' => ''
        ]);

        $this->assertSelectorTextContains('ul > li', 'You need to enter an URL.');
    }

    /** @test */
    public function original_should_not_be_a_valid_url()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('form')->form();
        $client->submit($form, [
            'url_form[original]' => 'invalid_url'
        ]);

        $this->assertSelectorTextContains('ul > li', 'The URL entered is not valid.');
    }
}
