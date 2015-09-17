<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExecutionServerType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('description', 'textarea', array(
            'required' => false,
            'icon' => 'info'
        ));
        $builder->add('artRunnerPath', 'text', array(
            'label' => 'ART Runner Path',
            'icon' => 'address'
        ));
        $builder->add('server', 'entity', array(
            'class' => 'AppMainBundle:Server',
            'property' => 'name',
            'icon' => 'desktop'
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
