<?php

namespace App\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PrerequisiteType extends AbstractType {

    private $application;
    private $test;
    private $em;

    public function __construct($test, $em) {
        $this->application = $test->getTestFolder()->getRootApplication();
        $this->test = $test;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $application = $this->application;
        $test = $this->test;
        $builder->add('test', 'entity', ['class' => 'AppMainBundle:Test',
            'property' => 'name',
            'group_by' => 'parentName',
            'label' => 'Scenario',
            'empty_value' => 'Select a scenario',
            'query_builder' => function(EntityRepository $er) use ($application, $test) {
                $qb = $er->createQueryBuilder('t')
                        ->join('t.application', 'a')
                        ->where('t != :test')
                        ->andWhere('a = :application')
                        ->setParameter('test', $test)
                        ->setParameter('application', $application)
                        ->addOrderBy('t.testFolder')
                        ->addOrderBy('t.name');
                $prerequisitesTestsId = $test->getPrerequisitesTestsId();
                if (count($prerequisitesTestsId) > 0) {
                    $qb->andWhere($qb->expr()->notIn('t.id', $prerequisitesTestsId));
                }
                return $qb;
            },
            'icon' => 'tasks'
        ]);
    }

    public function getName() {
        return 'prerequisite';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Prerequisite',
            'validation_group' => array(),
            'cascade_validation' => true
        ));
    }

}
