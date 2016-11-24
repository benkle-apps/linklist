<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as AbstractUser;

/**
 * Class User
 *
 * @package AppBundle\Entity
 * @ORM\Entity()
 * @ORM\Table(name="users")
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
}
