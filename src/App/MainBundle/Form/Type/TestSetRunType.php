<?php

namespace App\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TestSetRunType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('executionServer', 'entity', array(
            'class' => 'AppMainBundle:ExecutionServer',
            'property' => 'name',
            'empty_value' => 'Select an execution server',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('es')->orderBy('es.name', 'ASC');
            },
            'icon' => 'gauge'
        ));
    }

    public function getName() {
        return 'test_set_run';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\TestSetRun',
            'validation_group' => array(),
            'cascade_validation' => true
        ));
    }

}
