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
        $urlEntity->setDomain($host->getRegisterableDomain() ?: 'local')->setUrl($url);
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
            $this->getReposity()->createQueryBuilder('d')->select('d.domain')->addSelect('count(d.id) as c')->where(
                    'd.user = :user'
                )->setParameter('user', $user)->groupBy('d.domain')->orderBy('c', 'DESC')->getQuery();

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
        $qBuilder =
            $this->getReposity()
                ->createQueryBuilder('d')
                ->where('d.user = :user')
                ->setParameter('user', $user)
                ->orderBy('d.added');
        if (isset($options->domain)) {
            $qBuilder->andWhere('d.domain = :domain')->setParameter('domain', $options->domain);
        }

        switch ($options->gone) {
            case GetUrlsOptions::GONE_NOT:
                $qBuilder->andWhere('d.gone = false');
                break;
            case GetUrlsOptions::GONE_ONLY:
                $qBuilder->andWhere('d.gone = true');
                break;
        }

        switch ($options->visited) {
            case GetUrlsOptions::VISITED_NOT:
                $qBuilder->andWhere('d.visited is null');
                break;
            case GetUrlsOptions::GONE_ONLY:
                $qBuilder->andWhere('d.visited is not null');
                break;
        }

        if ($options->limit > 0) {
            $qBuilder->setMaxResults($options->limit);
        }

        if ($options->offset > 0) {
            $qBuilder->setFirstResult($options->offset);
        }

        return $qBuilder->getQuery()->getResult();
    }

    /**
     * UrlManager destructor.
     */
    public function __destruct()
    {
        $this->manager->flush();
    }

}
