<?php

namespace App\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('description', 'textarea', array(
            'required' => false,
            'icon' => 'info'
        ));
        $builder->add('objectType', 'entity', array(
            'class' => 'AppMainBundle:ObjectType',
            'property' => 'name',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('ot')->orderBy('ot.name', 'ASC');
            },
            'icon' => 'code'
        ));
        $builder->add('objectIdentifier', new ObjectIdentifierType(), array(
            'required' => false,
            'label' => 'Identification',
            'icon' => 'target'
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
