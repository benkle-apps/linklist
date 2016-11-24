<?php

namespace AppBundle\Service;

use AppBundle\Entity\Url;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use League\Uri\Components\Host;
use League\Uri\UriParser;

class UrlManager
{
    /** @var  EntityManager */
    private $manager;

    /** @var  UriParser */
    private $parser;

    /**
     * UrlManager constructor.
     *
     * @param EntityManager $manager
     * @param UriParser     $parser
     */
    public function __construct(EntityManager $manager, UriParser $parser)
    {
        $this->manager = $manager;
        $this->parser = $parser;
    }

    private function getReposity()
    {
        return $this->manager->getRepository('AppBundle:Url');
    }

    /**
     * @param User $user
     * @param string $url
     */
    public function addUrl(User $user, $url)
    {
        $urlEntity = new Url($user);
        $parsed = $this->parser->parse($url);
        $host = new Host($parsed['host']);
        $urlEntity
            ->setDomain($host->getRegisterableDomain() ?: 'local')
            ->setUrl($url);
        $this->manager->persist($urlEntity);
    }

    public function __destruct()
    {
        $this->manager->flush();
    }

}
