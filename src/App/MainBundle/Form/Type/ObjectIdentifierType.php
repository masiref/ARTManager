<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectIdentifierType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('objectIdentifierType', 'entity', array(
            'class' => 'AppMainBundle:ObjectIdentifierType',
            'property' => 'name',
            'label' => 'Type',
            'required' => false
        ));
        $builder->add('value');
    }

    public function getName() {
        return 'object_identifier';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ObjectIdentifier',
            'validation_group' => array(),
            'cascade_validation' => true
        ));
    }

}
