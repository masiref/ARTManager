<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ServerAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array(
                    'label' => 'Name'
                ))
                ->add('description', 'textarea', array(
                    'label' => 'Description',
                    'required' => false
                ))
                ->add('host', 'text', array(
                    'label' => 'Host'
                ))
                ->add('port', 'integer', array(
                    'label' => 'Port'
                ))
                ->add('username', 'text', array(
                    'label' => 'Username'
                ))
                ->add('password', 'text', array(
                    'label' => 'Password'
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper
                    ->add('executionServers', 'sonata_type_collection', array(
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
                ->add('host')
                ->add('port')
                ->add('username')
                ->add('password');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('host')
                ->add('port')
                ->add('username')
                ->add('password');
    }

}
