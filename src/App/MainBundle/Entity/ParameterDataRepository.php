<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ParameterDataRepository extends EntityRepository {

    public function alreadyExistsWithParameterInStep($parameter, $step) {
        $query = $this->createQueryBuilder('pd')
                ->where('pd.step = :step')
                ->andWhere('pd.parameter = :parameter')
                ->setParameter('step', $step)
                ->setParameter('parameter', $parameter)
                ->getQuery();
        return $query->getResult() != null;
    }

    public function findByObjectTypeAndAction($objectType, $action) {
        $query = $this->createQueryBuilder('ps')
                ->where('ps.objectType = :objectType')
                ->andWhere('ps.action = :action')
                ->setParameter('objectType', $objectType)
                ->setParameter('action', $action)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

}
