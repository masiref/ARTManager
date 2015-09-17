<?php

namespace App\MainBundle\Form\Type;

use App\MainBundle\Form\EventListener\AddExecuteStepActionFieldEventSubscriber;
use App\MainBundle\Form\EventListener\AddExecuteStepActionParametersFieldEventSubscriber;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExecuteStepType extends AbstractType {

    private $test;
    private $page;
    private $em;

    public function __construct($test, $em) {
        $this->test = $test;
        $this->page = $test->getActivePage();
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $step = $builder->getData();
        $page = $this->page;
        $test = $this->test;
        $order = $step->getOrder();
        if ($order != 0) {
            $page = $test->getPageAtStepOrder($order - 1);
        }

        $factory = $builder->getFormFactory();
        $em = $this->em;

        $addExecuteStepActionFieldEventSubscriber = new AddExecuteStepActionFieldEventSubscriber($factory, $em, $test);
        $addExecuteStepActionParametersFieldEventSubscriber = new AddExecuteStepActionParametersFieldEventSubscriber($factory, $em, $test);
        $builder->addEventSubscriber($addExecuteStepActionFieldEventSubscriber);
        $builder->addEventSubscriber($addExecuteStepActionParametersFieldEventSubscriber);

        $builder->add('object', 'entity', array(
            'class' => 'AppMainBundle:Object',
            'property' => 'name',
            'label' => 'Object',
            'empty_value' => '',
            'query_builder' => function(EntityRepository $er) use ($page) {
                return $er->createQueryBuilder('o')
                                ->where('o.page = :page')
                                ->setParameter('page', $page)
                                ->addOrderBy('o.name')
                ;
            },
            'attr' => array('data-test-id' => $test->getId()),
            'icon' => 'puzzle'
        ));
    }

    public function getName() {
        return 'execute_step';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ExecuteStep',
            'validation_group' => array(),
            'cascade_validation' => true
        ));
    }

}
