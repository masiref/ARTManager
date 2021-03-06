<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\ControlStep;
use App\MainBundle\Entity\ExecuteStep;
use App\MainBundle\Entity\Prerequisite;
use App\MainBundle\Entity\Test;
use App\MainBundle\Form\Type\ControlStepType;
use App\MainBundle\Form\Type\ExecuteStepType;
use App\MainBundle\Form\Type\PrerequisiteType;
use Doctrine\ORM\EntityRepository;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestEditorController extends BaseController {

    /**
     * @Route("/application/test/{id}/editor",
     *      name="app_index_application_test_editor",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function indexAction(Test $test) {
        $executeStep = new ExecuteStep();
        $executeStep->setTest($test);
        $application = $test->getTestFolder()->getRootApplication();
        $builder = $this->createFormBuilder($test);
        $standardPageType = $this->getDoctrine()->getManager()->getRepository('AppMainBundle:PageType')->findByName('Standard');
        $startingPageForm = $builder->add('startingPage', 'entity', array(
            'class' => 'AppMainBundle:Page',
            'property' => 'name',
            'group_by' => 'parentName',
            'empty_value' => 'Select a page',
            'query_builder' => function(EntityRepository $er) use ($application, $standardPageType) {
                return $er->createQueryBuilder('p')
                                ->join('p.objectMap', 'om')
                                ->join('om.application', 'a')
                                ->where('a = :application')
                                ->andWhere('p.pageType = :pageType')
                                ->setParameter('application', $application)
                                ->setParameter('pageType', $standardPageType)
                                ->orderBy('om.name')
                                ->addOrderBy('p.page')
                                ->addOrderBy('p.name');
            }
        ));

        $prerequisites = $test->getPrerequisites();
        if ($prerequisites->count() > 0) {
            $em = $this->getDoctrine()->getManager();
            $prerequisite = $prerequisites->get($prerequisites->count() - 1);
            $page = $prerequisite->getTest()->getActivePage();
            if ($test->getStartingPage() != $startingPageForm) {
                $test->setStartingPage($page);
                $em->persist($test);
                $em->flush();
            }
        }

        return $this->render('AppMainBundle:test:editor/index.html.twig', array(
                    'test' => $test,
                    'startingPageFormView' => $startingPageForm->getForm()->createView()
        ));
    }

    /**
     * @Route("/application/test/{id}/check/step",
     *      name="app_check_application_test_execute_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function checkExecuteStepAction(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ExecuteStepType($test, $em), new ExecuteStep());
            $form->handleRequest($request);
            if (!$form->isValid()) {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/execute/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/check/step/{id}/control/step",
     *      name="app_check_application_test_step_control_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ExecuteStep")
     */
    public function checkControlStepAction(ExecuteStep $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ControlStepType($step, $em), new ControlStep());
            $form->handleRequest($request);
            if (!$form->isValid()) {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/control/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/add/step",
     *      name="app_add_application_test_execute_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function addExecuteStepAction(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ExecuteStepType($test, $em), new ExecuteStep());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $step = $form->getData();
                $test->addStep($step);
                $em->persist($test);
                $em->flush();
                $ajaxResponse['id'] = $step->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/execute/item.html.twig', array(
                            'step' => $step
                        ))->getContent();
            } else {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/execute/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{id}/add/control",
     *      name="app_add_application_test_step_control_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ExecuteStep")
     */
    public function addControlStepAction(ExecuteStep $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ControlStepType($step, $em), new ControlStep());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $controlStep = $form->getData();
                $step->addControlStep($controlStep);
                $em->persist($step);
                $em->flush();
                $ajaxResponse['id'] = $controlStep->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/control/item.html.twig', array(
                            'controlStep' => $controlStep
                        ))->getContent();
                $ajaxResponse['testId'] = $step->getTest()->getId();
            } else {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/control/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{id}/delete",
     *      name="app_delete_application_test_execute_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ExecuteStep")
     */
    public function deleteExecuteStepAction(ExecuteStep $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
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
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/step/orders",
     *      name="app_get_application_test_execute_step_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getExecuteStepsOrders(Test $test, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $steps = $test->getSteps();
            foreach ($steps as $step) {
                $ajaxResponse[$step->getId()] = $step->getOrder();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/control/step/{id}/delete",
     *      name="app_delete_application_test_step_control_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ControlStep")
     */
    public function deleteControlStepAction(ControlStep $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
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
            $ajaxResponse['testId'] = $parentStep->getTest()->getId();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{id}/control/step/orders",
     *      name="app_get_application_test_step_control_step_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ExecuteStep")
     */
    public function getControlStepsOrders(ExecuteStep $step, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $controlSteps = $step->getControlSteps();
            foreach ($controlSteps as $controlStep) {
                $ajaxResponse[$controlStep->getId()] = $controlStep->getOrder();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/actions",
     *      name="app_get_application_test_step_actions_ajax",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
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
     * @Route("/application/test/{id}/update/starting/page",
     *      name="app_application_test_update_starting_page_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function updateStartingPage(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $pageId = $request->get("pageId");
            $page = $em->getRepository("AppMainBundle:Page")->find($pageId);
            if ($page !== null) {
                $test->setStartingPage($page);
                $em->persist($test);
                $em->flush();
            } else {
                $ajaxResponse['error'] = "This page does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{testId}/step/{id}/form",
     *      name="app_get_application_test_step_form_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true },
     *      defaults={"id" = -1}
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test", options={"id" = "testId"})
     */
    public function getExecuteStepFormAction($id, Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($id != -1) {
                $executeStep = $em->getRepository("AppMainBundle:ExecuteStep")->find($id);
                $executeStepFormView = $this->createForm(new ExecuteStepType($test, $em), $executeStep, array(
                            'method' => 'POST'
                        ))->createView();
                $modalTitleView = $this->render('AppMainBundle:test:step/execute/edit.modal-title.html.twig', array(
                    'step' => $executeStep
                ));
            } else {
                $executeStepFormView = $this->createForm(new ExecuteStepType($test, $em), new ExecuteStep(), array(
                            'method' => 'POST'
                        ))->createView();
                $modalTitleView = $this->render('AppMainBundle:test:step/execute/add.modal-title.html.twig', array(
                    'test' => $test
                ));
            }
            $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/execute/form_content.html.twig', array(
                        'form' => $executeStepFormView
                    ))->getContent();
            $ajaxResponse['modalTitle'] = $modalTitleView->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/update/step/{id}",
     *      name="app_update_application_test_execute_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ExecuteStep")
     */
    public function updateExecuteStepAction(ExecuteStep $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $test = $step->getTest();
            $form = $this->createForm(new ExecuteStepType($test, $em), $step);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $ajaxResponse['id'] = $step->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/execute/item.html.twig', array(
                            'step' => $step
                        ))->getContent();
                $ajaxResponse['testId'] = $test->getId();
            } else {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/execute/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/{stepId}/control/step/{id}/form",
     *      name="app_get_application_test_step_control_step_form_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true },
     *      defaults={"id" = -1}
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("step", class="AppMainBundle:ExecuteStep", options={"id" = "stepId"})
     */
    public function getControlStepFormAction($id, ExecuteStep $step, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($id != -1) {
                $controlStep = $em->getRepository("AppMainBundle:ControlStep")->find($id);
                $controlStepFormView = $this->createForm(new ControlStepType($step, $em), $controlStep, array(
                            'method' => 'POST'
                        ))->createView();
                $modalTitleView = $this->render('AppMainBundle:test:step/control/edit.modal-title.html.twig', array(
                    'controlStep' => $controlStep,
                    'step' => $step
                ));
            } else {
                $controlStepFormView = $this->createForm(new ControlStepType($step, $em), new ControlStep(), array(
                            'method' => 'POST'
                        ))->createView();
                $modalTitleView = $this->render('AppMainBundle:test:step/control/add.modal-title.html.twig', array(
                    'step' => $step
                ));
            }
            $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/control/form_content.html.twig', array(
                        'form' => $controlStepFormView
                    ))->getContent();
            $ajaxResponse['modalTitle'] = $modalTitleView->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/update/step/control/step/{id}",
     *      name="app_update_application_test_step_control_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("controlStep", class="AppMainBundle:ControlStep")
     */
    public function updateControlStepAction(ControlStep $controlStep, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $step = $controlStep->getParentStep();
            $form = $this->createForm(new ControlStepType($step, $em), $controlStep);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $ajaxResponse['id'] = $controlStep->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/control/item.html.twig', array(
                            'controlStep' => $controlStep
                        ))->getContent();
                $ajaxResponse['testId'] = $step->getTest()->getId();
            } else {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:step/control/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/prerequisite/form",
     *      name="app_get_application_test_prerequisite_form_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getPrerequisiteFormAction(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $prerequisiteFormView = $this->createForm(new PrerequisiteType($test, $em), new Prerequisite(), array(
                        'method' => 'POST'
                    ))->createView();
            $ajaxResponse['form'] = $this->render('AppMainBundle:test:prerequisite/form_content.html.twig', array(
                        'form' => $prerequisiteFormView
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/add/prerequisite",
     *      name="app_add_application_test_prerequisite_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function addPrerequisiteAction(Test $test, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new PrerequisiteType($test, $em), new Prerequisite());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $prerequisite = $form->getData();
                $test->addPrerequisite($prerequisite);
                $page = $prerequisite->getTest()->getActivePage();
                $test->setStartingPage($page);
                $em->persist($test);
                $em->flush();
                $ajaxResponse['id'] = $prerequisite->getId();
                $ajaxResponse['li'] = $this->render('AppMainBundle:test:prerequisite/item.html.twig', array(
                            'prerequisite' => $prerequisite
                        ))->getContent();
                if ($page != null) {
                    $ajaxResponse['startingPage'] = $page;
                } else {
                    $ajaxResponse['resetStartingPage'] = true;
                }
            } else {
                $ajaxResponse['form'] = $this->render('AppMainBundle:test:prerequisite/form_content.html.twig', array(
                            'form' => $form->createView()
                        ))->getContent();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/prerequisite/{id}/delete",
     *      name="app_delete_application_test_prerequisite_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("prerequisite", class="AppMainBundle:Prerequisite")
     */
    public function deletePrerequisiteAction(Prerequisite $prerequisite, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $parentTest = $prerequisite->getParentTest();
            $ajaxResponse['testId'] = $parentTest->getId();
            $changeTestStartingPage = $prerequisite->getOrder() == $parentTest->getPrerequisites()->count();
            $em->remove($prerequisite);
            $em->flush();
            $prerequisites = $parentTest->getPrerequisites();
            foreach ($prerequisites as $existingPrerequisite) {
                $order = $existingPrerequisite->getOrder();
                if ($order > $prerequisite->getOrder()) {
                    $existingPrerequisite->setOrder($order - 1);
                }
            }
            $page = null;
            if ($changeTestStartingPage) {
                if ($prerequisites->count() > 0) {
                    $page = $prerequisites->get($prerequisites->count() - 1)->getTest()->getActivePage();
                }
            }
            $parentTest->setStartingPage($page);
            if ($page != null) {
                $ajaxResponse['startingPage'] = $page;
            } else {
                $ajaxResponse['resetStartingPage'] = true;
            }
            $em->persist($parentTest);
            $em->flush();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/prerequisites/orders",
     *      name="app_get_application_test_prerequisites_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getPrerequisitesOrdersAction(Test $test, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $prerequisites = $test->getPrerequisites();
            foreach ($prerequisites as $prerequisite) {
                $ajaxResponse[$prerequisite->getId()] = $prerequisite->getOrder();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/{id}/behat/scenario",
     *      name="app_get_application_test_behat_scenario_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getBehatScenarioAction(Test $test, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["scenario"] = $this->render('AppMainBundle:test:editor/behat_content.html.twig', array(
                        'test' => $test
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/orders/update",
     *      name="app_update_application_test_execute_step_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateExecuteStepsOrdersAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $stepsAndOrders = array();
            $steps = explode(",", $request->get('steps'));
            $order = 1;
            foreach ($steps as $stepId) {
                $step = $em->getRepository("AppMainBundle:ExecuteStep")->find($stepId);
                $step->setOrder($order);
                $em->persist($step);
                $stepsAndOrders[$stepId] = $order;
                $order += 1;
            }
            $em->flush();
            $ajaxResponse["stepsAndOrders"] = $stepsAndOrders;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/step/control/step/orders/update",
     *      name="app_update_application_test_step_control_step_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateControlStepsOrdersAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $stepsAndOrders = array();
            $steps = explode(",", $request->get('steps'));
            $order = 1;
            foreach ($steps as $stepId) {
                $step = $em->getRepository("AppMainBundle:ControlStep")->find($stepId);
                $step->setOrder($order);
                $em->persist($step);
                $stepsAndOrders[$stepId] = $order;
                $order += 1;
            }
            $em->flush();
            $ajaxResponse["stepsAndOrders"] = $stepsAndOrders;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
