<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as AbstractUser;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class User
 *
 * @package AppBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(name="users")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(
 *          name="salt",
 *          column=@ORM\Column(name="salt", type="string", nullable=true)
 *      )
 * })
 * @Serializer\ExclusionPolicy("all")
 */
class User extends AbstractUser
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $key;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
