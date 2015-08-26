<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ExecutionServerAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array(
                    'label' => 'Name'
                ))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false
                ))
                ->add('artRunnerPath', 'text', array(
                    'label' => 'ART Runner Path'
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper->add('server', 'sonata_type_model', array(
                'btn_add' => false
            ));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
                ->add('artRunnerPath')
                ->add('server');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('artRunnerPath')
                ->add('server');
    }

}
