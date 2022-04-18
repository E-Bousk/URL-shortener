<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\UrlType;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UrlsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods={"GET", "POST"})
     */
    public function create(Request $request, EntityManagerInterface $em, UrlRepository $urlRepository): Response
    {
        $form = $this->createForm(UrlType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $url = $urlRepository->findOneBy(['original' => $form['original']->getData()]);

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
                // Utilisation d'un « entity listener » (pre-persist) pour setter le 'shortened' (voir « UrlEntityListener.php »)
                // $url->setShortened($this->getUniqueShortenedString());
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
}
