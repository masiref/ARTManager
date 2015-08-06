<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ObjectIdentifierAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('value', 'text', array(
                    'label' => 'Value'
        ));
        if (!$this->hasParentFieldDescription()) {
            $formMapper->add('objectIdentifierType', 'sonata_type_model', array(
                'btn_add' => false
            ));
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('value')
                ->add('objectIdentifierType');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('value')
                ->add('objectIdentifierType');
    }

}
