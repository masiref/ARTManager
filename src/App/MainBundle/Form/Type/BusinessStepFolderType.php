<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BusinessStepFolderType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', array(
            'icon' => 'pencil'
        ));
        $builder->add('description', 'textarea', array(
            'required' => false,
            'icon' => 'info'
        ));
    }

    public function getName() {
        return 'business_step_folder';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\BusinessStepFolder',
            'validation_groups' => array('Default', 'business_step_folder_application', 'business_step_folder_business_step_folder'),
            'cascade_validation' => true
        ));
    }

}
