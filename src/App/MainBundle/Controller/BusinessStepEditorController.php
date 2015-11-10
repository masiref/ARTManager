<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Controller\BaseController;
use App\MainBundle\Entity\BusinessStep;
use App\MainBundle\Entity\ControlStep;
use App\MainBundle\Entity\ExecuteStep;
use App\MainBundle\Entity\StepSentence;
use App\MainBundle\Form\Type\ControlStepType;
use App\MainBundle\Form\Type\ExecuteStepType;
use App\MainBundle\Form\Type\StepSentenceType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessStepEditorController extends BaseController {

    /**
     * @Route("/application/business/step/{id}/editor",
     *      name="app_index_application_business_step_editor",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function indexAction(BusinessStep $businessStep) {
        $application = $businessStep->getBusinessStepFolder()->getRootApplication();
        $builder = $this->createFormBuilder($businessStep);
        $standardPageType = $this->getDoctrine()->getManager()->getRepository('AppMainBundle:PageType')->findByName('Standard');
        $modalPageType = $this->getDoctrine()->getManager()->getRepository('AppMainBundle:PageType')->findByName('Modal');
        $pageTypes = array($standardPageType, $modalPageType);
        $startingPageForm = $builder->add('startingPage', 'entity', array(
            'class' => 'AppMainBundle:Page',
            'property' => 'name',
            'group_by' => 'parentName',
            'empty_value' => 'Select a page',
            'query_builder' => function(EntityRepository $er) use ($application, $pageTypes) {
                return $er->createQueryBuilder('p')
                                ->join('p.objectMap', 'om')
                                ->join('om.application', 'a')
                                ->where('a = :application')
                                ->andWhere('p.pageType in (:pageTypes)')
                                ->setParameter('application', $application)
                                ->setParameter('pageTypes', $pageTypes)
                                ->orderBy('om.name')
                                ->addOrderBy('p.page')
                                ->addOrderBy('p.name');
            }
        ));
        $addStepSentenceFormView = $this->createForm(new StepSentenceType(), new StepSentence(), array(
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:business-step:editor/index.html.twig', array(
                    'businessStep' => $businessStep,
                    'startingPageFormView' => $startingPageForm->getForm()->createView(),
                    'addStepSentenceFormView' => $addStepSentenceFormView
        ));
    }

    /**
     * @Route("/application/business/step/{id}/check/step",
     *      name="app_check_application_business_step_execute_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function checkExecuteStepAction(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ExecuteStepType($businessStep, $em), new ExecuteStep());
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
     * @Route("/application/business/step/check/step/{id}/control/step",
     *      name="app_check_application_business_step_step_control_step_ajax",
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
     * @Route("/application/business/step/{id}/add/step",
     *      name="app_add_application_business_step_execute_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function addExecuteStepAction(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ExecuteStepType($businessStep, $em), new ExecuteStep());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $step = $form->getData();
                $businessStep->addStep($step);
                $businessStep->updateParameterSet(new LifecycleEventArgs($businessStep, $em));
                $em->persist($businessStep);
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
     * @Route("/application/business/step/step/{id}/add/control",
     *      name="app_add_application_business_step_step_control_step_ajax",
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
                $businessStep = $step->getBusinessStep();
                $businessStep->updateParameterSet(new LifecycleEventArgs($businessStep, $em));
                $em->persist($businessStep);
                $em->flush();
                $ajaxResponse['id'] = $controlStep->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/control/item.html.twig', array(
                            'controlStep' => $controlStep
                        ))->getContent();
                $ajaxResponse['testId'] = $step->getBusinessStep()->getId();
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
     * @Route("/application/business/step/step/{id}/delete",
     *      name="app_delete_application_business_step_execute_step_ajax",
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
            $businessStep = $step->getBusinessStep();
            $businessStepSteps = $businessStep->getSteps();
            foreach ($businessStepSteps as $businessStepStep) {
                $order = $businessStepStep->getOrder();
                if ($order > $step->getOrder()) {
                    $businessStepStep->setOrder($order - 1);
                }
            }
            $em->persist($businessStep);
            $em->flush();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/{id}/step/orders",
     *      name="app_get_application_business_step_execute_step_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function getExecuteStepsOrders(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $steps = $businessStep->getSteps();
            foreach ($steps as $step) {
                $ajaxResponse[$step->getId()] = $step->getOrder();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/step/control/step/{id}/delete",
     *      name="app_delete_application_business_step_step_control_step_ajax",
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
            $ajaxResponse['testId'] = $parentStep->getBusinessStep()->getId();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/step/{id}/control/step/orders",
     *      name="app_get_application_business_step_step_control_step_orders_ajax",
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
     * @Route("/application/business/step/step/actions",
     *      name="app_get_application_business_step_step_actions_ajax",
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
     * @Route("/application/business/step/{id}/update/starting/page",
     *      name="app_application_business_step_update_starting_page_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function updateStartingPage(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $pageId = $request->get("pageId");
            $page = $em->getRepository("AppMainBundle:Page")->find($pageId);
            if ($page !== null) {
                $businessStep->setStartingPage($page);
                $em->persist($businessStep);
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
     * @Route("/application/business/step/{testId}/step/{id}/form",
     *      name="app_get_application_business_step_step_form_ajax",
     *      options={"expose" = true },
     *      defaults={"id" = -1}
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep", options={"id" = "testId"})
     */
    public function getExecuteStepFormAction($id, BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($id != -1) {
                $executeStep = $em->getRepository("AppMainBundle:ExecuteStep")->find($id);
                $executeStepFormView = $this->createForm(new ExecuteStepType($businessStep, $em), $executeStep, array(
                            'method' => 'POST'
                        ))->createView();
                $modalTitleView = $this->render('AppMainBundle:test:step/execute/edit.modal-title.html.twig', array(
                    'step' => $executeStep
                ));
            } else {
                $executeStepFormView = $this->createForm(new ExecuteStepType($businessStep, $em), new ExecuteStep(), array(
                            'method' => 'POST'
                        ))->createView();
                $modalTitleView = $this->render('AppMainBundle:test:step/execute/add.modal-title.html.twig', array(
                    'test' => $businessStep
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
     * @Route("/application/business/step/update/step/{id}",
     *      name="app_update_application_business_step_execute_step_ajax",
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
            $businessStep = $step->getBusinessStep();
            $form = $this->createForm(new ExecuteStepType($businessStep, $em), $step);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $businessStep->updateParameterSet(new LifecycleEventArgs($businessStep, $em));
                $em->persist($businessStep);
                $em->flush();
                $ajaxResponse['id'] = $step->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/execute/item.html.twig', array(
                            'step' => $step
                        ))->getContent();
                $ajaxResponse['testId'] = $businessStep->getId();
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
     * @Route("/application/business/step/step/{stepId}/control/step/{id}/form",
     *      name="app_get_application_business_step_step_control_step_form_ajax",
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
     * @Route("/application/business/step/update/step/control/step/{id}",
     *      name="app_update_application_business_step_step_control_step_ajax",
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
                $businessStep = $step->getBusinessStep();
                $businessStep->updateParameterSet(new LifecycleEventArgs($businessStep, $em));
                $em->persist($businessStep);
                $em->flush();
                $ajaxResponse['id'] = $controlStep->getId();
                $ajaxResponse['row'] = $this->render('AppMainBundle:test:step/control/item.html.twig', array(
                            'controlStep' => $controlStep
                        ))->getContent();
                $ajaxResponse['testId'] = $step->getBusinessStep()->getId();
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
     * @Route("/application/business/step/step/orders/update",
     *      name="app_update_application_business_step_execute_step_orders_ajax",
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
     * @Route("/application/business/step/step/control/step/orders/update",
     *      name="app_update_application_business_step_step_control_step_orders_ajax",
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

    /**
     * @Route("/application/business/step/sentence/{id}/update/sentence",
     *      name="app_application_business_step_sentence_update_sentence_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateSentenceAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $stepSentence = $em->getRepository('AppMainBundle:StepSentence')->find($request->get("pk"));
            if ($stepSentence != null) {
                $stepSentence->setSentence($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($stepSentence);
                if (count($errors) == 0) {
                    $em->persist($stepSentence);
                    $em->flush();
                    $sentence = "" . $stepSentence;
                    $response = new Response(json_encode($sentence));
                } else {
                    $message = "";
                    foreach ($errors as $err) {
                        if ($message !== "") {
                            $message .= "\n";
                        }
                        $message .= $err->getMessage();
                    }
                    $response = new Response($message, 400);
                }
            } else {
                $response = new Response("Unknown sentence", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/add/business/step/{id}/add/sentence",
     *      name="app_add_application_business_step_sentence_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function addSentenceAction(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new StepSentenceType(), new StepSentence());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $stepSentence = $form->getData();
                $stepSentenceGroup = $businessStep->getStepSentenceGroup();
                $stepSentenceGroup->addSentence($stepSentence);
                $em->persist($stepSentenceGroup);
                $em->flush();
                $ajaxResponse['sentencesContent'] = $this->render('AppMainBundle:business-step:editor/sentences-content.html.twig', array(
                            'stepSentenceGroup' => $stepSentenceGroup,
                            'addStepSentenceFormView' => $this->createForm(new StepSentenceType(), new StepSentence(), array(
                                'method' => 'POST'
                            ))->createView()
                        ))->getContent();
            } else {
                $ajaxResponse['error'] = Utility::getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/add/business/step/{id}/delete/sentence/{sentenceId}",
     *      name="app_delete_application_business_step_sentence_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     * @ParamConverter("stepSentence", class="AppMainBundle:StepSentence", options={"id" = "sentenceId"})
     */
    public function deleteSentenceAction(BusinessStep $businessStep, StepSentence $stepSentence, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em->remove($stepSentence);
            $em->flush();
            $ajaxResponse['sentencesContent'] = $this->render('AppMainBundle:business-step:editor/sentences-content.html.twig', array(
                        'stepSentenceGroup' => $businessStep->getStepSentenceGroup(),
                        'addStepSentenceFormView' => $this->createForm(new StepSentenceType(), new StepSentence(), array(
                            'method' => 'POST'
                        ))->createView()
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
