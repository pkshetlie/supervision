<?php
/**
 * Created by PhpStorm.
 * User: pierr
 * Date: 21/10/2016
 * Time: 23:03
 */

namespace App\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\CoreBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends EntityUserCore
{


    public function __construct()
    {
        parent::__construct();
    }

    public function getMd5Email(){
        return md5($this->email);
    }

}
