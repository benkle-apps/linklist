<?php

namespace AppBundle\Service;

class GetUrlsOptions
{
    const GONE_ONLY = 0;
    const GONE_NOT = 1;
    const GONE_BOTH = 2;

    const VISITED_ONLY = 0;
    const VISITED_NOT = 1;
    const VISITED_BOTH = 2;

    public $domain = null;
    public $limit = 0;
    public $offset = 0;
    public $gone = self::GONE_BOTH;
    public $visited = self::VISITED_BOTH;
}
