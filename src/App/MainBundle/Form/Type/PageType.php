<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name');
        $builder->add('description', 'textarea', array(
            'required' => false
        ));
        $builder->add('pageType', 'entity', array(
            'class' => 'AppMainBundle:PageType',
            'property' => 'name',
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
