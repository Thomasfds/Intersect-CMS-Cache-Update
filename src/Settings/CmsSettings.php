<?php

/**
 * Intersect CMS Unleashed
 * 2.2 Cache Update
 * Last modify : 24/08/2021 at 20:21
 * Author : XFallSeane
 * Website : https://intersect.thomasfds.fr
 */

namespace App\Settings;

use App\Entity\CmsPages;
use App\Entity\CmsSettings as EntityCmsSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CmsSettings
{
    private $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getGamePage()
    {
        $gamePage = $this->em->getRepository(CmsPages::class)->findBy(['category' => "game", 'isVisible' => 1]);
        return $gamePage;
    }

    public function getWikiPage()
    {
        $gamePage = $this->em->getRepository(CmsPages::class)->findBy(['category' => "wiki", 'isVisible' => 1]);
        return $gamePage;
    }

    public function get($param)
    {
        return $this->em->getRepository(EntityCmsSettings::class)->findOneBy(['setting' => $param])->getDefaultValue();
    }

    public function getCache($name){
        $cache = new FilesystemAdapter();

        return $cache->getItem($name)->get();
    }
}
