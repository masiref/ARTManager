<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PageAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array(
                    'label' => 'Name'
                ))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false
                ))
                ->add('path', 'text', array(
                    'label' => 'Path'
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper
                    ->add('pageType', 'sonata_type_model', array(
                        'btn_add' => false
                    ))
                    ->add('objectMap', 'sonata_type_model', array(
                        'btn_add' => false
                    ))
                    ->add('page', 'sonata_type_model', array(
                        'label' => 'Parent',
                        'btn_add' => false,
                        'required' => false
                    ))
                    ->add('pages', 'sonata_type_collection', array(
                        'label' => 'Sub Pages',
                        'by_reference' => false,
                        'type_options' => array(
                            'delete' => true
                        )), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                    ))
                    ->add('objects', 'sonata_type_collection', array(
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
                ->add('path')
                ->add('pageType')
                ->add('objectMap')
                ->add('page');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('path')
                ->add('pageType')
                ->add('objectMap')
                ->add('page');
    }

}
