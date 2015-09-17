<?php

namespace App\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
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
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('ot')->orderBy('ot.name', 'ASC');
            }
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
