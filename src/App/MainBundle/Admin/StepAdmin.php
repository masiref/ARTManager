<?php

namespace App\MainBundle\Admin;

use App\MainBundle\Entity\ControlStep;
use App\MainBundle\Entity\ExecuteStep;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class StepAdmin extends Admin {

    protected function configureFormFields(FormMapper $formMapper) {
        $subject = $this->getSubject();
        $formMapper
                ->add('order', 'integer', array(
                    'label' => 'Order'
        ));
        if (!$this->hasParentFieldDescription()) {
            if ($subject instanceof ExecuteStep) {
                $formMapper->add('test', 'sonata_type_model', array(
                            'btn_add' => false
                        ))
                        ->add('action', 'sonata_type_model', array(
                            'btn_add' => false
                        ))
                        ->add('object', 'sonata_type_model', array(
                            'btn_add' => false
                ));
            } elseif ($subject instanceof ControlStep) {
                $formMapper->add('parentStep', 'sonata_type_model', array(
                            'btn_add' => false
                        ))
                        ->add('action', 'sonata_type_model', array(
                            'btn_add' => false
                        ))
                        ->add('page', 'sonata_type_model', array(
                            'btn_add' => false
                        ))
                        ->add('object', 'sonata_type_model', array(
                            'btn_add' => false
                ));
            }
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('order')
                ->add('test')
                ->add('parentStep');
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('order')
                ->add('test')
                ->add('parentStep');
    }

}
