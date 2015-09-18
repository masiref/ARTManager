<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApplicationType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('description', 'textarea', array(
            'required' => false,
            'icon' => 'info'
        ));
        $builder->add('url', 'url', array(
            'required' => false,
            'icon' => 'link'
        ));
    }

    public function getName() {
        return 'application';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Application',
            'validation_groups' => array('Default', 'application'),
            'cascade_validation' => true
        ));
    }

}
