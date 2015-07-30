<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('description', 'textarea', array(
            'required' => false
        ));
        $builder->add('objectType', 'entity', array(
            'class' => 'AppMainBundle:ObjectType',
            'property' => 'name',
        ));
        $builder->add('objectIdentifier', new ObjectIdentifierType(), array(
            'label' => 'Identification'
        ));
    }

    public function getName() {
        return 'object';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Object',
            'validation_group' => array('object'),
            'cascade_validation' => true
        ));
    }

}
