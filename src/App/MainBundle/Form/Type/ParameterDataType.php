<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParameterDataType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('value', 'text', array(
            'label' => "Value"
        ));
    }

    public function getName() {
        return 'parameter_data';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ParameterData',
            'validation_group' => array(''),
            'cascade_validation' => true
        ));
    }

}
