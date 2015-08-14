<?php

namespace App\MainBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class StepSentenceAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('sentence', 'text', array(
                    'label' => 'Sentence'
                ))
                ->add('minkSentence', 'text', array(
                    'label' => 'Mink Sentence',
                    'required' => false
                ))
                ->add('locale', 'text', array(
                    'label' => 'Locale'
        ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('sentence')
                ->add('minkSentence')
                ->add('locale');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('sentence')
                ->add('minkSentence')
                ->add('locale');
    }

}
