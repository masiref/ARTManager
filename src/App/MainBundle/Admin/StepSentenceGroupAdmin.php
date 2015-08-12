<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class StepSentenceGroupAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper->add('action', 'sonata_type_model', array(
                    'btn_add' => false
                ))
                ->add('objectType', 'sonata_type_model', array(
                    'btn_add' => false,
                    'required' => false
                ))
                ->add('pageType', 'sonata_type_model', array(
                    'btn_add' => false,
                    'required' => false
                ))
                ->add('sentences', 'sonata_type_model', array(
                    'multiple' => true,
                    'compound' => false,
                    'required' => false
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('action')
                ->add('objectType')
                ->add('pageType')
                ->add('sentences');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('action')
                ->add('objectType')
                ->add('pageType')
                ->add('sentences');
    }

}
