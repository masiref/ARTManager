<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ApplicationAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array('label' => 'Name'))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false))
                ->add('url', 'url', array(
                    'label' => 'URL',
                    'required' => false))
                ->add('objectMaps', 'sonata_type_collection', array(
                    'by_reference' => false,
                    'type_options' => array(
                        // Prevents the "Delete" option from being displayed
                        'delete' => true
                    )
                        ), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper->add('project', 'sonata_type_model', array(
                'btn_add' => false
            ));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
                ->add('url')
                ->add('project')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('url')
                ->add('project')
        ;
    }

}
