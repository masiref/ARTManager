<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ObjectTypeAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('name', 'text', array('label' => 'Name'))
                ->add('icon', 'text', array('label' => 'Icon'));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('name')
                ->add('icon')
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('name')
                ->add('icon')
        ;
    }

}
