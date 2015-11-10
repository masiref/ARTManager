<?php

namespace App\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ParameterRepository extends EntityRepository {

    public function findByNameInParameterSet($name, $parameterSet) {
        $query = $this->createQueryBuilder('p')
                ->where('p.parameterSet = :parameterSet')
                ->andWhere('p.name = :name')
                ->setParameter('parameterSet', $parameterSet)
                ->setParameter('name', $name)
                ->getQuery();
        return $query->getOneOrNullResult();
    }

}
