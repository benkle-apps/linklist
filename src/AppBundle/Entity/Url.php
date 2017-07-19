<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Url
 *
 * @package AppBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(
 *     name="urls",
 *     indexes={
 *         @ORM\Index(name="domain_idx", columns={"domain", "user_id", "gone", "deleted"}),
 *         @ORM\Index(name="user_id_idx", columns={"id", "user_id"})
 *     }
 * )
 * @Serializer\ExclusionPolicy("all")
 */
class Url
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $domain;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $added;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $visited;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $gone = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     * @Serializer\Groups({"display"})
     * @Serializer\Expose()
     */
    private $deleted = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    private $user;

    /**
     * Url constructor.
     */
    public function __construct(User $user = null)
    {
        $this->added = new \DateTime();
        $this->user  = $user;
    }

    /**
     * @return boolean
     */
    public function isGone()
    {
        return $this->gone;
    }

    /**
     * @param boolean $gone
     *
     * @return Url
     */
    public function setGone($gone)
    {
        $this->gone = $gone;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Url
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return Url
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getVisited()
    {
        return $this->visited;
    }

    /**
     * @param \DateTime $visited
     *
     * @return Url
     */
    public function setVisited($visited = null)
    {
        $this->visited = isset($visited) ?: new \DateTime();

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }
}
