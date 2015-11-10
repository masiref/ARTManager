<?php

namespace App\MainBundle\Form\Type;

use App\MainBundle\Entity\Test;
use App\MainBundle\Form\EventListener\AddExecuteStepActionFieldEventSubscriber;
use App\MainBundle\Form\EventListener\AddExecuteStepActionParametersFieldEventSubscriber;
use App\MainBundle\Form\EventListener\AddExecuteStepBusinessStepParametersFieldEventSubscriber;
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
        $application = $test->getApplication();
        $order = $step->getOrder();
        if ($order != 0) {
            $page = $test->getPageAtStepOrder($order - 1);
        }

        $factory = $builder->getFormFactory();
        $em = $this->em;

        $addExecuteStepActionFieldEventSubscriber = new AddExecuteStepActionFieldEventSubscriber($factory, $em, $test);
        $addExecuteStepActionParametersFieldEventSubscriber = new AddExecuteStepActionParametersFieldEventSubscriber($factory, $em, $test);
        $addExecuteStepBusinessStepParametersFieldEventSubscriber = new AddExecuteStepBusinessStepParametersFieldEventSubscriber($factory, $em, $test);
        $builder->addEventSubscriber($addExecuteStepActionFieldEventSubscriber);
        $builder->addEventSubscriber($addExecuteStepActionParametersFieldEventSubscriber);
        $builder->addEventSubscriber($addExecuteStepBusinessStepParametersFieldEventSubscriber);

        $builder->add('object', 'entity', array(
            'class' => 'AppMainBundle:Object',
            'property' => 'name',
            'label' => 'Object',
            'group_by' => 'containerName',
            'empty_value' => '',
            'query_builder' => function(EntityRepository $er) use ($page) {
                $qb = $er->createQueryBuilder('o');
                $or = $qb->expr()->andX(
                        $qb->expr()->isNull('p.page'), $qb->expr()->eq('pt.name', ':containerType'), $qb->expr()->eq('p.objectMap', ':objectMap')
                );
                $or1 = $qb->expr()->andX(
                        $qb->expr()->eq('p.page', ':page'), $qb->expr()->eq('pt.name', ':containerType')
                );
                $qb->leftJoin('o.page', 'p')
                        ->join('p.pageType', 'pt')
                        ->where('p = :page')
                        ->orWhere($or)
                        ->orWhere($or1)
                        ->setParameter('page', $page)
                        ->setParameter('containerType', 'Container')
                        ->setParameter('objectMap', $page->getRootObjectMap())
                        ->addOrderBy('p.page')
                        ->addOrderBy('p.name')
                        ->addOrderBy('o.name')
                ;
                return $qb;
            },
            'attr' => array('data-test-id' => $test->getId()),
            'icon' => 'puzzle'
        ));

        if ($test instanceof Test) {
            $builder->add('businessStep', 'entity', array(
                'class' => 'AppMainBundle:BusinessStep',
                'property' => 'name',
                'label' => 'Business step',
                'group_by' => 'parentName',
                'empty_value' => '',
                'query_builder' => function(EntityRepository $er) use ($application, $page) {
                    return $er->createQueryBuilder('bs')
                                    ->where('bs.application = :application')
                                    ->andWhere('bs.startingPage = :page')
                                    ->setParameter('application', $application)
                                    ->setParameter('page', $page);
                },
                'attr' => array('data-test-id' => $test->getId()),
                'icon' => 'level-up'
            ));
        }
    }

    public function getName() {
        return 'execute_step';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ExecuteStep',
            'validation_group' => array(),
            'cascade_validation' => true,
            'allow_extra_fields' => true
        ));
    }

}
