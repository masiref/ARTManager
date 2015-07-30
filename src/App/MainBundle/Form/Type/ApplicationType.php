<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ApplicationType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('description', 'textarea', array(
            'required' => false
        ));
        $builder->add('url', 'url', array(
            'required' => false
        ));
    }

    public function getName() {
        return 'application';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Application',
            'validation_group' => array('application'),
            'cascade_validation' => true
        ));
    }

}
