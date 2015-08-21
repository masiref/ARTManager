<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TestSetFolderAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array(
                    'label' => 'Name'
                ))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper
                    ->add('application', 'sonata_type_model', array(
                        'btn_add' => false,
                        'required' => false
                    ))
                    ->add('testSetFolder', 'sonata_type_model', array(
                        'label' => 'Parent',
                        'btn_add' => false,
                        'required' => false
                    ))
                    ->add('testSetFolders', 'sonata_type_collection', array(
                        'label' => 'Sub Folders',
                        'by_reference' => false,
                        'type_options' => array(
                            'delete' => true
                        )), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                    ))
                    ->add('testSets', 'sonata_type_collection', array(
                        'label' => 'Test Sets',
                        'by_reference' => false,
                        'type_options' => array(
                            'delete' => true
                        )), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
            ));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
                ->add('application')
                ->add('testSetFolder');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('application')
                ->add('testSetFolder');
    }

}
