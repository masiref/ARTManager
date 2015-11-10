<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ParameterSetAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper->add('action', 'sonata_type_model', array(
                    'btn_add' => false,
                    'required' => false
                ))
                ->add('objectType', 'sonata_type_model', array(
                    'btn_add' => false,
                    'required' => false
                ))
                ->add('pageType', 'sonata_type_model', array(
                    'btn_add' => false,
                    'required' => false
                ))
                ->add('businessStep', 'sonata_type_model', array(
                    'btn_add' => false,
                    'required' => false
                ))
                ->add('parameters', 'sonata_type_collection', array(
                    'label' => 'Parameters',
                    'required' => false,
                    'by_reference' => false,
                    'type_options' => array(
                        'delete' => true
                    )), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('action')
                ->add('objectType')
                ->add('pageType')
                ->add('businessStep')
                ->add('parameters');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('action')
                ->add('objectType')
                ->add('pageType')
                ->add('businessStep')
                ->add('parameters');
    }

}
