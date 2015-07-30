<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ObjectAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array('label' => 'Name'))
                ->add('description', 'text', array(
                    'label' => 'Description',
                    'required' => false));
        if (!$this->hasParentFieldDescription()) {
            $formMapper->add('page', 'sonata_type_model', array(
                'btn_add' => false
            ));
            $formMapper->add('objectType', 'sonata_type_model', array(
                'btn_add' => false
            ));
        }
        $formMapper->add('objectIdentifier', 'sonata_type_model_list', array());
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('description')
                ->add('page')
                ->add('objectType')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('description')
                ->add('page')
                ->add('objectType')
        ;
    }

}
