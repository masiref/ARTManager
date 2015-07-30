<?php

namespace App\MainBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExecuteStepType extends AbstractType {

    private $page;

    public function __construct($page) {
        $this->page = $page;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $page = $this->page;

        $builder->add('name');
        $builder->add('description', 'textarea', array(
            'required' => false
        ));
        $builder->add('object', 'entity', array(
            'class' => 'AppMainBundle:Object',
            'property' => 'name',
            'label' => 'Object',
            'empty_value' => '',
            'query_builder' => function(EntityRepository $er) use ($page) {
                return $er->createQueryBuilder('o')
                                ->where('o.page = :page')
                                ->setParameter('page', $page)
                ;
            }
        ));
    }

    public function getName() {
        return 'execute_step';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ExecuteStep',
            'validation_group' => array('step_test', 'step_step'),
            'cascade_validation' => true
        ));
    }

}
