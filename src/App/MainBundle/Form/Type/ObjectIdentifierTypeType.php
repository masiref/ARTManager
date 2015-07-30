<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectIdentifierTypeType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('description');
    }

    public function getName() {
        return 'object_identifier_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ObjectIdentifierType',
            'validation_group' => array('object_identifier_type'),
            'cascade_validation' => true
        ));
    }

}
