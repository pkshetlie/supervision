<?php

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class RepositoryCore extends \Doctrine\ORM\EntityRepository
{

    protected function CreateDataTableQuery(Request $request = null, $th = null, User $user = null)
    {
        $qb = $this->createQueryBuilder('l');
        $or = array();
        if ($request != null) {
            $search = $request->get('search', null);
            if ($search != null && !empty($search['value'])) {
                foreach ($th AS $k => $field) {
                    $or = $qb->expr()->like("l." . $field, ":search");
                }
                $qb->setParameter('search', $search['value'] . "%");
            }
            $order = $request->get('order', null);
            if ($order != null && isset($order[0]) && isset($order[0]['column'])) {
                $qb->orderBy("l." . $th[$order[0]['column']], $order[0]['dir']);
            }
        }
        if ($user != null) {
            if(isset($or) && !empty($or)){
                $qb->add("where", $qb->expr()->andX($qb->expr()->eq("l.user", ":user"), $qb->expr()->orX($or)))
                    ->setParameter('user', $user);
            }else{
                $qb->add("where", $qb->expr()->andX($qb->expr()->eq("l.user", ":user")))
                    ->setParameter('user', $user);
            }
        } elseif (isset($or) && !empty($or)) {
            $qb->add("where", $or);
        }

        return $qb;
    }

    public function count(User $user = null)
    {
        $qb = $this->CreateDataTableQuery(null, null, $user)->select("COUNT(l)");
        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllForDataTable(Request $request, $th, User $user = null)
    {
        return $this->CreateDataTableQuery($request, $th, $user)
            ->select('l')
            ->setMaxResults($request->get('length', 10))
            ->setFirstResult($request->get('start', 0))
            ->getQuery()
            ->getResult();

    }

}
