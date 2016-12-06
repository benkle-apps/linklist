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
        $this->parser  = $parser;
    }

    /**
     * @param User   $user
     * @param string $url
     */
    public function addUrl(User $user, $url)
    {
        $urlEntity = new Url($user);
        $parsed    = $this->parser->parse($url);
        $host      = new Host($parsed['host']);
        $urlEntity->setDomain($host->getRegisterableDomain() ?: 'local')
                  ->setUrl($url);
        $this->manager->persist($urlEntity);
    }

    /**
     * @param User $user
     *
     * @return string[]
     */
    public function getDomains(User $user)
    {
        $q =
            $this->getReposity()
                 ->createQueryBuilder('d')
                 ->select('d.domain')
                 ->addSelect('count(d.id) as c')
                 ->where(
                     'd.user = :user'
                 )
                 ->setParameter('user', $user)
                 ->groupBy('d.domain')
                 ->orderBy('c', 'DESC')
                 ->getQuery();

        return $q->getArrayResult();
    }

    private function getReposity()
    {
        return $this->manager->getRepository('AppBundle:Url');
    }

    /**
     * @param User   $user
     * @param string $domain
     * @param int    $limit
     * @param int    $offset
     *
     * @return Url[]
     */
    public function getUrlsSimple(User $user, $domain = null, $limit = 0, $offset = 0)
    {
        $options         = new GetUrlsOptions();
        $options->domain = $domain;
        $options->limit  = $limit;
        $options->offset = $offset;

        return $this->getUrls($user, $options);
    }

    /**
     * @param User           $user
     * @param GetUrlsOptions $options
     *
     * @return Url[]
     */
    public function getUrls(User $user, GetUrlsOptions $options)
    {
        $query = new UrlQuery($this->getReposity());
        $query->setUser($user->getId() !== null ? $user : null)
              ->setDomain($options->domain)
              ->setVisitedHandling($options->visited)
              ->setGoneHandling($options->gone)
              ->setLimit($options->limit)
              ->setOffset($options->offset)
              ->setOrderBy($options->order)
              ->setOrderDirection($options->orderDirection);

        return $query->get();
    }

    /**
     * @param User           $user
     * @param GetUrlsOptions $options
     *
     * @return int
     */
    public function countUrls(User $user, GetUrlsOptions $options)
    {
        $query = new UrlQuery($this->getReposity());
        $query->setUser($user->getId() !== null ? $user : null)
              ->setDomain($options->domain)
              ->setVisitedHandling($options->visited)
              ->setGoneHandling($options->gone)
              ->setLimit($options->limit)
              ->setOffset($options->offset)
              ->setOrderBy($options->order)
              ->setOrderDirection($options->orderDirection);

        return $query->count();
    }

    public function storeUrl(Url $url)
    {
        $this->manager->persist($url);
    }

    /**
     * UrlManager destructor.
     */
    public function __destruct()
    {
        $this->manager->flush();
    }

}
