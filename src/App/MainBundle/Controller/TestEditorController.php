<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\ControlStep;
use App\MainBundle\Entity\ExecuteStep;
use App\MainBundle\Entity\Step;
use App\MainBundle\Entity\Test;
use App\MainBundle\Form\Type\ControlStepType;
use App\MainBundle\Form\Type\ExecuteStepType;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestEditorController extends Controller {

    /**
     * @Route("/application/test/{id}/editor", name="app_index_application_test_editor", options={"expose" = true })
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function indexAction(Test $test) {
        $em = $this->getDoctrine()->getManager();
        $executeStep = new ExecuteStep();
        $executeStep->setTest($test);
        $addExecuteStepFormView = $this->createForm(new ExecuteStepType($test->getActivePage()), $executeStep, array(
                    'method' => 'POST'
                ))->createView();
        $addControlStepFormView = $this->createForm(new ControlStepType(), new ControlStep(), array(
                    'method' => 'POST'
                ))->createView();
        $application = $test->getTestFolder()->getRootApplication();
        $builder = $this->createFormBuilder($test);
        $startingPageForm = $builder->add('startingPage', 'entity', array(
            'class' => 'AppMainBundle:Page',
            'property' => 'name',
            'group_by' => 'parentName',
            'empty_value' => 'Select a page',
            'query_builder' => function(EntityRepository $er) use ($application) {
                return $er->createQueryBuilder('p')
                                ->join('p.objectMap', 'om')
                                ->join('om.application', 'a')
                                ->where('a = :application')
                                ->setParameter('application', $application)
                                ->orderBy('om.name')
                                ->addOrderBy('p.page')
                                ->addOrderBy('p.name');
            }
        ));
        return $this->render('AppMainBundle:test:editor/index.html.twig', array(
                    'test' => $test,
                    'addExecuteStepFormView' => $addExecuteStepFormView,
                    'addControlStepFormView' => $addControlStepFormView,
                    'startingPageFormView' => $startingPageForm->getForm()->createView()
        ));
    }

    /**
     * @Route("/application/test/{id}/add/step",
     *          name="app_add_application_test_execute_step_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function addExecuteStepAction($test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($test !== null) {
                $form = $this->createForm(new ExecuteStepType(), new ExecuteStep());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {
                        $step = $form->getData();
                        $test->addStep($step);
                        $em->persist($test);
                        $em->flush();
                        $ajaxResponse['id'] = $step->getId();
                        $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/execute/item.html.twig', array(
                                    'step' => $step
                                ))->getContent();
                    } catch (DBALException $e) {
                        if ($test->getName() == null || $test->getName() == "") {
                            $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                        } else {
                            $ajaxResponse['error'] = "ERROR: Name already used.";
                        }
                    }
                } else {
                    $ajaxResponse['error'] = (string) $form->getErrors(true);
                }
            } else {
                $ajaxResponse['error'] = "This test does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{id}/add/control",
     *          name="app_add_application_test_step_control_step_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("step", class="AppMainBundle:Step")
     */
    public function addControlStepAction(Step $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($step !== null) {
                $form = $this->createForm(new ControlStepType(), new ControlStep());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    //try {
                    $controlStep = $form->getData();
                    $step->addControlStep($controlStep);
                    $em->persist($step);
                    $em->flush();
                    $ajaxResponse['id'] = $controlStep->getId();
                    $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/control/item.html.twig', array(
                                'controlStep' => $controlStep
                            ))->getContent();
                    /* } catch (DBALException $e) {
                      if ($step->getName() == null || $step->getName() == "") {
                      $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                      } else {
                      $ajaxResponse['error'] = "ERROR: Name already used.";
                      }
                      } */
                } else {
                    $ajaxResponse['error'] = (string) $form->getErrors(true);
                }
            } else {
                $ajaxResponse['error'] = "This step does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{id}/delete", name="app_delete_application_test_execute_step_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("step", class="AppMainBundle:Step")
     */
    public function deleteExecuteStepAction(Step $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($step !== null) {
                $controlSteps = $step->getControlSteps();
                foreach ($controlSteps as $controlStep) {
                    $em->remove($controlStep);
                    $em->flush();
                }
                $em->remove($step);
                $em->flush();
                $test = $step->getTest();
                $testSteps = $test->getSteps();
                foreach ($testSteps as $testStep) {
                    $order = $testStep->getOrder();
                    if ($order > $step->getOrder()) {
                        $testStep->setOrder($order - 1);
                    }
                }
                $em->persist($test);
                $em->flush();
            } else {
                $ajaxResponse['error'] = "This step does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/step/orders", name="app_get_application_test_execute_step_orders_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getExecuteStepsOrders(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($test !== null) {
                $steps = $test->getSteps();
                foreach ($steps as $step) {
                    $ajaxResponse[$step->getId()] = $step->getOrder();
                }
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/control/step/{id}/delete", name="app_delete_application_test_step_control_step_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("step", class="AppMainBundle:Step")
     */
    public function deleteControlStepAction(Step $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($step !== null) {
                $em->remove($step);
                $em->flush();
                $parentStep = $step->getParentStep();
                $controlSteps = $parentStep->getControlSteps();
                foreach ($controlSteps as $controlStep) {
                    $order = $controlStep->getOrder();
                    if ($order > $step->getOrder()) {
                        $controlStep->setOrder($order - 1);
                    }
                }
                $em->persist($parentStep);
                $em->flush();
            } else {
                $ajaxResponse['error'] = "This step does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{id}/control/step/orders", name="app_get_application_test_step_control_step_orders_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("step", class="AppMainBundle:Step")
     */
    public function getControlStepsOrders(Step $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($step !== null) {
                $controlSteps = $step->getControlSteps();
                foreach ($controlSteps as $controlStep) {
                    $ajaxResponse[$controlStep->getId()] = $controlStep->getOrder();
                }
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/actions", name="app_get_application_test_step_actions_ajax", options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function getActions(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'GET') {
            $ajaxResponse = $em->getRepository('AppMainBundle:Action')->findBy([], ['name' => 'ASC']);
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/update/starting/page", name="app_application_test_update_starting_page_ajax", options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function updateStartingPage(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($test !== null) {
                $pageId = $request->get("pageId");
                $page = $em->getRepository("AppMainBundle:Page")->find($pageId);
                if ($page !== null) {
                    $test->setStartingPage($page);
                    $em->persist($test);
                    $em->flush();
                    $executeStep = new ExecuteStep();
                    $executeStep->setTest($test);
                    $addExecuteStepFormView = $this->createForm(new ExecuteStepType($test->getActivePage()), $executeStep, array(
                                'method' => 'POST'
                            ))->createView();
                    $ajaxResponse['addExecuteStepForm'] = $this->render('AppMainBundle:test:step/execute/new.form.html.twig', array(
                                'addExecuteStepFormView' => $addExecuteStepFormView
                            ))->getContent();
                } else {
                    $ajaxResponse['error'] = "This page does not exist.";
                }
            } else {
                $ajaxResponse['error'] = "This test does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
