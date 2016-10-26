<?php

namespace App\ProjectBundle\Repository;

use App\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * ErrorRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class ErrorRepository extends EntityRepository
{
    public function getNew(User $user){
        $qb = $this->createQueryBuilder("e")
        ->where("e.dateCreated > :lastLogin")
        ->setParameter("lastLogin",$user->getLastLogin());

        return $qb->getQuery()->getResult();
    }

}
