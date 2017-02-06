<?php

namespace AppBundle\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class Domain
 * This is not really a normal entity, but it's useful for proper OOP.
 *
*@package AppBundle\Entity
 */
class Domain
{
    /**
     * @Groups({"display"})
     * @var string
     */
    private $name = '';

    /**
     * @Groups({"display"})
     * @var int
     */
    private $count = 0;

    /**
     * Domain constructor.
     *
     * @param string $name
     * @param int    $count
     */
    public function __construct($name, $count)
    {
        $this->name  = $name;
        $this->count = $count;
    }

    /**
     * Get the domain name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the URL count for this domain.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

}
