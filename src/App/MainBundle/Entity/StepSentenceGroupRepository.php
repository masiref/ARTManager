<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StepSentenceGroupRepository extends EntityRepository {

    public function findByObjectTypeAndAction($objectType, $action) {
        $query = $this->createQueryBuilder('ssg')
                ->where('ssg.objectType = :objectType')
                ->andWhere('ssg.action = :action')
                ->setParameter('objectType', $objectType)
                ->setParameter('action', $action)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function findByPageTypeAndAction($pageType, $action) {
        $query = $this->createQueryBuilder('ssg')
                ->where('ssg.pageType = :pageType')
                ->andWhere('ssg.action = :action')
                ->setParameter('pageType', $pageType)
                ->setParameter('action', $action)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

}
