<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TestSetRunRepository extends EntityRepository {

    public function findPlannedOrderByCreatedAt() {
        $query = $this->createQueryBuilder('tsr')
                ->leftJoin('tsr.status', 's')
                ->where('tsr.status is NULL')
                ->orWhere('s.name = :queued')
                ->orWhere('s.name = :running')
                ->setParameter('queued', "Queued")
                ->setParameter('running', "Running")
                ->orderBy('tsr.createdAt', 'ASC')
                ->getQuery();
        return $query->getResult();
    }

}
