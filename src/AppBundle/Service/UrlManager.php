<?php

namespace AppBundle\Service;

use AppBundle\Entity\Domain;
use AppBundle\Entity\Url;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
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
     * Add an URL for a user.
     *
     * @param UserInterface   $user
     * @param string $url
     */
    public function addUrl(UserInterface $user, $url)
    {
        $urlEntity = new Url($user);
        $parsed    = $this->parser->parse($url);
        $host      = new Host($parsed['host']);
        $urlEntity->setDomain($host->getRegisterableDomain() ?: 'local')
                  ->setUrl($url);
        $this->manager->persist($urlEntity);
    }

    /**
     * Get the list of domains for which the user has URLs stored.
     *
     * @param UserInterface $user
     *
     * @return Domain[]
     */
    public function getDomains(UserInterface $user)
    {
        $q =
            $this->getRepository()
                 ->createQueryBuilder('d')
                 ->select('d.domain')
                 ->addSelect('count(d.id) as c')
                 ->where('d.user = :user')
                 ->andWhere('d.deleted = false')
                 ->setParameter('user', $user)
                 ->groupBy('d.domain')
                 ->orderBy('c', 'DESC')
                 ->getQuery();

        $result = [];
        foreach ($q->getArrayResult() as $item) {
            $result[] = new Domain($item['domain'], $item['c']);
        }
        return $result;
    }

    private function getRepository()
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
    public function getUrlsSimple(UserInterface $user, $domain = null, $limit = 0, $offset = 0)
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
        $query = new UrlQuery($this->getRepository());
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
    public function countUrls(UserInterface $user, GetUrlsOptions $options)
    {
        $query = new UrlQuery($this->getRepository());
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

    public function getQuery()
    {
        return new UrlQuery($this->getRepository());
    }

    /**
     * @param UserInterface $user
     * @param $id
     * @return Url|null|object
     */
    public function getUrl(UserInterface $user, $id)
    {
        return $this->getRepository()->findOneBy(['id' => $id, 'user' => $user]);
    }

}
