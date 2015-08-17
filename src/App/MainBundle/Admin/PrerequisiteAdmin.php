<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PrerequisiteAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper->add('test', 'sonata_type_model', array(
            'btn_add' => false
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('order')
                ->add('test');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('order')
                ->add('test');
    }

}
