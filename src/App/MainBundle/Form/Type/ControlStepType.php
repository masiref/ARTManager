<?php

namespace App\MainBundle\Form\Type;

use App\MainBundle\Form\EventListener\AddControlStepActionFieldEventSubscriber;
use App\MainBundle\Form\EventListener\AddControlStepActionParametersFieldEventSubscriber;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ControlStepType extends AbstractType {

    private $step;
    private $page;
    private $em;

    public function __construct($step, $em) {
        $this->step = $step;
        $this->page = $step->getActivePage();
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $controlStep = $builder->getData();
        $page = $this->page;
        $application = $page->getRootObjectMap()->getApplication();
        $step = $this->step;
        $order = $controlStep->getOrder();
        if ($order != 0) {
            $page = $step->getPageAtControlStepOrder($order - 1);
        }

        $factory = $builder->getFormFactory();
        $em = $this->em;

        $addControlStepActionFieldEventSubscriber = new AddControlStepActionFieldEventSubscriber($factory, $em, $step);
        $addControlStepActionParametersFieldEventSubscriber = new AddControlStepActionParametersFieldEventSubscriber($factory, $em, $step);
        $builder->addEventSubscriber($addControlStepActionFieldEventSubscriber);
        $builder->addEventSubscriber($addControlStepActionParametersFieldEventSubscriber);

        $builder->add('page', 'entity', array(
            'class' => 'AppMainBundle:Page',
            'property' => 'name',
            'label' => 'Page',
            'group_by' => 'parentName',
            'empty_value' => '',
            'query_builder' => function(EntityRepository $er) use ($application) {
                return $er->createQueryBuilder('p')
                                ->join('p.objectMap', 'om')
                                ->join('om.application', 'a')
                                ->where('a = :application')
                                ->setParameter('application', $application)
                                ->orderBy('om.name')
                                ->addOrderBy('p.page')
                                ->addOrderBy('p.name');
            },
            'attr' => array('data-step-id' => $step->getId()),
            'icon' => 'th-large'
        ));

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
            'attr' => array('data-step-id' => $step->getId()),
            'icon' => 'puzzle'
        ));
    }

    public function getName() {
        return 'control_step';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'App\MainBundle\Entity\ControlStep',
            'validation_group' => array(),
            'cascade_validation' => true
        ));
    }

}
