<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExecutionServerType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('description', 'textarea', array(
            'required' => false
        ));
        $builder->add('artRunnerPath', 'text', array(
            'label' => 'ART Runner Path'
        ));
        $builder->add('server', 'entity', array(
            'class' => 'AppMainBundle:Server',
            'property' => 'name',
        ));
    }

    public function getName() {
        return 'execution_server';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ExecutionServer',
            'validation_group' => array(),
            'cascade_validation' => true
        ));
    }

}
