<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServerType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('description', 'textarea', array(
            'required' => false,
            'icon' => 'info'
        ));
        $builder->add('host', 'text', array(
            'icon' => 'address'
        ));
        $builder->add('port', 'integer', array(
            'icon' => 'lifebuoy'
        ));
        $builder->add('username', 'text', array(
            'icon' => 'user'
        ));
        $builder->add('password', 'text', array(
            'icon' => 'key'
        ));
    }

    public function getName() {
        return 'server';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Server'
        ));
    }

}
