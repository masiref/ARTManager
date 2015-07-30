<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TestType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('description', 'textarea', array(
            'required' => false
        ));
    }

    public function getName() {
        return 'test';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Test',
            'validation_group' => array('test'),
            'cascade_validation' => true
        ));
    }

}
