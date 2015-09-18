<?php

namespace App\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('description', 'textarea', array(
            'required' => false,
            'icon' => 'info'
        ));
        $builder->add('pageType', 'entity', array(
            'class' => 'AppMainBundle:PageType',
            'property' => 'name',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('pt')->orderBy('pt.name', 'ASC');
            },
            'icon' => 'code'
        ));
        $builder->add('path', 'text', array(
            'label' => 'Path',
            'icon' => 'address',
            'help' => 'When the selected type is "Modal", specify the title of it.'
        ));
    }

    public function getName() {
        return 'page';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\Page',
            'validation_group' => array('page_page'),
            'cascade_validation' => true
        ));
    }

}
