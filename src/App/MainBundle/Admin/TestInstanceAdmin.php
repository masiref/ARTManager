<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TestInstanceAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('order', 'integer', array(
                    'label' => 'Order'
                ))
                ->add('test', 'sonata_type_model', array(
                    'btn_add' => false
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper
                    ->add('testSet', 'sonata_type_model', array(
                        'btn_add' => false
            ));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('order')
                ->add('test')
                ->add('testSet');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('order')
                ->add('test')
                ->add('testSet');
    }

}
