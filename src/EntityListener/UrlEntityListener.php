<?php

namespace App\EntityListener;

use App\Entity\Url;
use Illuminate\Support\Str;
use App\Repository\UrlRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UrlEntityListener
{
    private UrlRepository $urlRepository;

    public function __construct(UrlRepository $urlRepository)
    {
        $this->urlRepository = $urlRepository;
    }

    public function prePersist(Url $url, LifecycleEventArgs $event)
    {
        // ‼ NOTE : ajout d'une condition car on set 'shortened' dans les tests ‼
        if (!$url->getShortened()) {
            $url->setShortened($this->getUniqueShortenedString());
        }
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
