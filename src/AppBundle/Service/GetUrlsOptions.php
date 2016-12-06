<?php

namespace AppBundle\Service;

class GetUrlsOptions
{
    const GONE_ONLY = 1;
    const GONE_NOT = 2;
    const GONE_BOTH = 0;

    const VISITED_ONLY = 4;
    const VISITED_NOT = 8;
    const VISITED_BOTH = 0;

    const ORDER_UP = 1;
    const ORDER_DOWN = -1;

    const ORDER_BY_ADDED = 1;
    const ORDER_BY_VISITED = 2;

    public $domain = null;
    public $limit = 0;
    public $offset = 0;
    public $gone = self::GONE_BOTH;
    public $visited = self::VISITED_BOTH;
    public $order = self::ORDER_BY_ADDED;
    public $orderDirection = self::ORDER_UP;
}
