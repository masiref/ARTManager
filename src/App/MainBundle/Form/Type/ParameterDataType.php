<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParameterDataType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($builder) {
            $form = $event->getForm();
            $data = $event->getData();
            $form->add(
                    $builder->getFormFactory()->createNamed('value', 'text', null, array(
                        'label' => $data->getParameter()->getName(),
                        'auto_initialize' => false
            )));
        });
        $builder->add('value');
    }

    public function getName() {
        return 'parameter_data';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ParameterData',
            'validation_group' => array(''),
            'cascade_validation' => true
        ));
    }

}
