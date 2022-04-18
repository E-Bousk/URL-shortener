<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\UrlFormType;
use Illuminate\Support\Str;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UrlsController extends AbstractController
{
    private UrlRepository $urlRepository;

    public function __construct(UrlRepository $urlRepository)
    {
        $this->urlRepository = $urlRepository;
    }

    /**
     * @Route("/", name="app_home", methods={"GET", "POST"})
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UrlFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $this->urlRepository->findOneBy(['original' => $form['original']->getData()]);

            // if (!$url) {
            //     $url = (new Url)
            //         ->setOriginal($form['original']->getData())
            //         ->setShortened($this->getUniqueShortenedString())
            //     ;
            //     $em->persist($url);
            //     $em->flush();
            // }
            
            if (!$url) {
                $url = $form->getData();
                $url->setShortened($this->getUniqueShortenedString());
                $em->persist($url);
                $em->flush();
            }
            
            return $this->redirectToRoute('app_urls_preview', ['shortened' => $url->getShortened()]);
        }

        return $this->render('urls/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{shortened}/preview", name="app_urls_preview", methods="GET")
     */
    public function preview(Url $url): Response
    {
        return $this->render('urls/preview.html.twig', compact('url'));
    }

    /**
     * @Route("/{shortened}", name="app_urls_show", methods="GET")
     */
    public function show(Url $url): Response
    {
        return $this->redirect($url->getOriginal());
    }

    private function getUniqueShortenedString(): string
    {
        $shortened = Str::random(6);

        if ($this->urlRepository->findOneBy(compact('shortened'))) {
            return $this->getUniqueShortenedString();
        }

        return $shortened;
    }
}
