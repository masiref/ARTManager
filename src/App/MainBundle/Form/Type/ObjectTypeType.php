<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectTypeType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('icon');
    }

    public function getName() {
        return 'object_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ObjectType',
            'validation_group' => array('object_type'),
            'cascade_validation' => true
        ));
    }

}
