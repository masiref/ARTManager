<?php

namespace App\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StepSentenceType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('locale', 'text', array(
            'icon' => 'language'
        ));
        $builder->add('sentence', 'textarea', array(
            'icon' => 'pencil'
        ));
    }

    public function getName() {
        return 'step_sentence';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\StepSentence'
        ));
    }

}
