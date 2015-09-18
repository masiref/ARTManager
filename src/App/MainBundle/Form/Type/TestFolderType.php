<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TestFolderType extends AbstractType {

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
        return 'test_folder';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\TestFolder',
            'validation_groups' => array('Default', 'test_folder_application', 'test_folder_test_folder'),
            'cascade_validation' => true
        ));
    }

}
