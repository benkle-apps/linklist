<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UrlQuery
{
    const ALL           = 0;
    const GONE_ONLY     = 1;
    const NOT_GONE_ONLY = 2;

    const VISITED_ONLY     = 4;
    const NOT_VISITED_ONLY = 8;

    const ORDER_UP   = 1;
    const ORDER_DOWN = -1;

    const ORDER_BY_ADDED   = 1;
    const ORDER_BY_VISITED = 2;

    /** @var string|null */
    private $domain = null;
    /** @var int */
    private $limit = 0;
    /** @var int */
    private $offset = 0;
    /** @var int */
    private $goneHandling = self::ALL;
    /** @var int */
    private $visitedHandling = self::ALL;
    /** @var int */
    private $orderBy = self::ORDER_BY_ADDED;
    /** @var int */
    private $orderDirection = self::ORDER_UP;
    /** @var User|null */
    private $user = null;
    /** @var EntityRepository */
    private $repository = null;

    /**
     * UrlQuery constructor.
     *
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return null|string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param null|string $domain
     *
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int
     */
    public function getGoneHandling()
    {
        return $this->goneHandling;
    }

    /**
     * @param int $goneHandling
     *
     * @return $this
     */
    public function setGoneHandling($goneHandling)
    {
        $this->goneHandling = $goneHandling;

        return $this;
    }

    /**
     * @return int
     */
    public function getVisitedHandling()
    {
        return $this->visitedHandling;
    }

    /**
     * @param int $visitedHandling
     *
     * @return $this
     */
    public function setVisitedHandling($visitedHandling)
    {
        $this->visitedHandling = $visitedHandling;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param int $orderBy
     *
     * @return $this
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * @param int $orderDirection
     *
     * @return $this
     */
    public function setOrderDirection($orderDirection)
    {
        $this->orderDirection = $orderDirection;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function get()
    {
        return $this->createQueryBuilder()
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Create and fill query builder.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createQueryBuilder()
    {
        $builder = $this->repository->createQueryBuilder('d');
        if (isset($this->user)) {
            $builder->where('d.user = :user')
                    ->setParameter('user', $this->user);
        }

        if (isset($this->domain)) {
            $builder->andWhere('d.domain = :domain')
                    ->setParameter('domain', $this->domain);
        }

        switch ($this->goneHandling) {
            case self::NOT_GONE_ONLY:
                $builder->andWhere('d.gone = false');
                break;
            case self::GONE_ONLY:
                $builder->andWhere('d.gone = true');
                break;
        }

        switch ($this->visitedHandling) {
            case self::NOT_VISITED_ONLY:
                $builder->andWhere('d.visited is null');
                break;
            case self::VISITED_ONLY:
                $builder->andWhere('d.visited is not null');
                break;
        }

        if ($this->limit > 0) {
            $builder->setMaxResults($this->limit);
        }

        if ($this->offset > 0) {
            $builder->setFirstResult($this->offset);
        }

        $orderDirection = $this->orderDirection == GetUrlsOptions::ORDER_UP ? 'ASC' : 'DESC';

        switch ($this->orderBy) {
            case self::ORDER_BY_ADDED:
                $builder->orderBy('d.added', $orderDirection);
                break;
            case self::ORDER_BY_VISITED:
                $builder->orderBy('d.visited', $orderDirection);
                break;
        }

        return $builder;
    }

    public function count()
    {
        $result =
            $this->createQueryBuilder()
                 ->select('count(d.id)')
                 ->getQuery()
                 ->getScalarResult();

        while (is_array($result)) {
            $result = reset($result);
        }
        return intval($result, 10);
    }
}
