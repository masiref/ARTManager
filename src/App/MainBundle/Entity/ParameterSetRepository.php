<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ParameterSetRepository extends EntityRepository {

    public function findByObjectTypeAndAction($objectType, $action) {
        $query = $this->createQueryBuilder('ps')
                ->where('ps.objectType = :objectType')
                ->andWhere('ps.action = :action')
                ->setParameter('objectType', $objectType)
                ->setParameter('action', $action)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function findByPageTypeAndAction($pageType, $action) {
        $query = $this->createQueryBuilder('ps')
                ->where('ps.pageType = :pageType')
                ->andWhere('ps.action = :action')
                ->setParameter('pageType', $pageType)
                ->setParameter('action', $action)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function findByBusinessStep($businessStep) {
        $query = $this->createQueryBuilder('ps')
                ->where('ps.businessStep = :businessStep')
                ->setParameter('businessStep', $businessStep)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

}
