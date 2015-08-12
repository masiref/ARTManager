<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ParameterAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array(
                    'label' => 'Name'
                ))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false
                ))
                ->add('order', 'number', array(
                    'label' => 'Order'
                ))
                ->add('placeholder', 'text', array(
                    'label' => 'Placeholder'
                ))
                ->add('mandatory', 'checkbox', array(
                    'required' => false
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper->add('parameterSet', 'sonata_type_model', array(
                'btn_add' => false
            ));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
                ->add('order')
                ->add('placeholder')
                ->add('mandatory');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('order')
                ->add('placeholder')
                ->add('mandatory');
    }

}
