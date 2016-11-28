<?php

namespace App\CoreBundle\Repository;

use App\CoreBundle\Entity\User;
use Gedmo\Sortable\Entity\Repository\SortableRepository;
use Symfony\Component\HttpFoundation\Request;

class RepositoryCore extends SortableRepository
{

    public function count(User $user = null, Request $request = null, $th = null)
    {
        $qb = $this->CreateDataTableQuery($request, $th, $user)->select("COUNT(l)");
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

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->findBy(array("isDeleted" => 0), array("position" => "asc"));
    }

    protected function CreateDataTableQuery(Request $request = null, $th = null, User $user = null)
    {
        $qb = $this->createQueryBuilder('l');
        $or = null;
        $order = $request != null ? $request->get('order', null) : null;

        if ($request != null) {
            $search = $request->get('search', null);
            if ($search != null && !empty($search['value'])) {
                foreach ($th AS $k => $field) {
                    $or = $qb->expr()->orX($qb->expr()->like("l." . $field, ":search"), $or);
                }
                $qb->setParameter('search', $search['value'] . "%");
            }
            if ($order != null && isset($order[0]) && isset($order[0]['column'])) {
                $qb->orderBy("l." . $th[$order[0]['column']], $order[0]['dir']);
            }
        }
        if ($user != null) {
            if (isset($or) && !empty($or)) {
                $qb->add("where", $qb->expr()->andX($qb->expr()->eq("l.user", ":user"), $or))
                    ->setParameter('user', $user);
            } else {
                $qb->add("where", $qb->expr()->andX($qb->expr()->eq("l.user", ":user")))
                    ->setParameter('user', $user);
            }
        } elseif (isset($or) && !empty($or)) {
            $qb->where($or);
        }
        $qb->andWhere("l.isDeleted = 0");
        if ($order == null) {
            $qb->orderBy("l.position", "ASC");
        }
        return $qb;
    }
}
