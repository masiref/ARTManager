<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ActionAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array(
                    'label' => 'Name'
                ))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false
                ))
                ->add('actionType', 'sonata_type_model', array(
                    'btn_add' => false
                ))
                ->add('objectTypes', 'sonata_type_model', array(
                    'multiple' => true,
                    'compound' => false,
                    'required' => false
                ))
                ->add('pageTypes', 'sonata_type_model', array(
                    'multiple' => true,
                    'compound' => false,
                    'required' => false
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
                ->add('actionType')
                ->add('objectTypes')
                ->add('pageTypes');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('actionType')
                ->add('objectTypes')
                ->add('pageTypes');
    }

}
