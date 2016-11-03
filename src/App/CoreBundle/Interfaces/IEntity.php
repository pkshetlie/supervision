<?php
/**
 * Created by PhpStorm.
 * User: pierr
 * Date: 28/08/2016
 * Time: 20:43
 */

namespace App\CoreBundle\Interfaces;


use FOS\UserBundle\Model\UserInterface;

interface IEntity
{
    public function getId();
    public function getUser();
    public function setUser(UserInterface $user);

}