<?php

namespace AppBundle\Service;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer as SymfonySerializer;

class Serializer
{
    /** @var SymfonySerializer */
    private $serializer;

    /**
     * Serializer constructor.
     *
     * @param SymfonySerializer $serializer
     */
    public function __construct(SymfonySerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function json($data, $groups = [])
    {
        $context = new SerializationContext();
        $context->setGroups($groups);
        return \json_decode($this->serializer->serialize($data, 'json'));
    }

}
