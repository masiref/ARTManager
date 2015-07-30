<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ProjectAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array('label' => 'Name'))
                ->add('description', 'textarea', array(
                    'label' => 'Description',
                    'required' => false
                ))
                ->add('applications', 'sonata_type_collection', array(
                    'by_reference' => false,
                    'type_options' => array(
                        // Prevents the "Delete" option from being displayed
                        'delete' => true
                    )
                        ), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
        ;
    }

}
