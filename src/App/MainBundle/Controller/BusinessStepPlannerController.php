<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\BusinessStep;
use App\MainBundle\Entity\BusinessStepFolder;
use App\MainBundle\Entity\ParameterSet;
use App\MainBundle\Entity\StepSentence;
use App\MainBundle\Entity\StepSentenceGroup;
use App\MainBundle\Form\Type\BusinessStepFolderType;
use App\MainBundle\Form\Type\BusinessStepType;
use App\MainBundle\Utility;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessStepPlannerController extends BaseController {

    /**
     * @Route("/application/{id}/business/step/planner", name="app_index_application_business_step_planner")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function indexAction(Application $application) {
        $addBusinessStepFolderFormView = $this->createForm(new BusinessStepFolderType(), new BusinessStepFolder(), array(
                    'method' => 'POST'
                ))->createView();
        $addBusinessStepFormView = $this->createForm(new BusinessStepType(), new BusinessStep(), array(
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:business-step:planner/index.html.twig', array(
                    'application' => $application,
                    'addBusinessStepFolderFormView' => $addBusinessStepFolderFormView,
                    'addBusinessStepFormView' => $addBusinessStepFormView
        ));
    }

    /**
     * @Route("/application/{id}/business/steps/tree",
     *      name="app_application_get_business_steps_tree_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function getBusinessStepsTreeAction(Application $application, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["tree-business-steps-" . $application->getId()] = $application->getJsonBusinessStepsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/steps/tree/selected/folder/{id}",
     *      name="app_application_get_business_steps_tree_with_selected_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStepFolder", class="AppMainBundle:BusinessStepFolder")
     */
    public function getBusinessStepsTreeWithSelectedFolderAction(BusinessStepFolder $businessStepFolder, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $businessStepFolder->setSelected(true);
            $application = $businessStepFolder->getRootApplication();
            $ajaxResponse["tree-business-steps-" . $application->getId()] = $application->getJsonBusinessStepsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/steps/tree/selected/business/step/{id}",
     *      name="app_application_get_business_steps_tree_with_selected_business_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function getBusinessStepsTreeWithSelectedBusinessStepAction(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $businessStep->setSelected(true);
            $application = $businessStep->getBusinessStepFolder()->getRootApplication();
            $ajaxResponse["tree-business-steps-" . $application->getId()] = $application->getJsonBusinessStepsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/add/business/step/folder/{parentId}",
     *      name="app_add_application_business_step_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true },
     *      defaults={"parentId" = -1}
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function addBusinessStepFolderAction(Application $application, $parentId, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $formBusinessStepFolder = new BusinessStepFolder();
            if ($parentId != -1) {
                $parentBusinessStepFolder = $em->getRepository("AppMainBundle:BusinessStepFolder")->find($parentId);
                $formBusinessStepFolder->setBusinessStepFolder($parentBusinessStepFolder);
            } else {
                $formBusinessStepFolder->setApplication($application);
            }
            $form = $this->createForm(new BusinessStepFolderType(), $formBusinessStepFolder);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $businessStepFolder = $form->getData();
                $em->persist($businessStepFolder);
                $businessStepFolder->setSelected(true);
                $em->flush();
                $ajaxResponse['id'] = $businessStepFolder->getId();
                $ajaxResponse['name'] = $businessStepFolder->getName();
                $ajaxResponse['description'] = $businessStepFolder->getDescription();
                $ajaxResponse['businessStepFoldersCount'] = $application->getBusinessStepFoldersCount();
                $ajaxResponse['treeBusinessSteps'] = $application->getJsonBusinessStepsTreeAsArray();
            } else {
                $ajaxResponse['error'] = Utility::getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/{id}",
     *      name="app_application_get_business_step_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStepFolder", class="AppMainBundle:BusinessStepFolder")
     */
    public function getBusinessStepFolderAction(BusinessStepFolder $businessStepFolder, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["businessStepFolder"] = $businessStepFolder;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/business/step/{id}",
     *      name="app_application_get_business_step_folder_business_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStep", class="AppMainBundle:BusinessStep")
     */
    public function getBusinessStepAction(BusinessStep $businessStep, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["businessStep"] = $businessStep;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/{id}/update/name",
     *      name="app_application_business_step_folder_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateBusinessStepFolderNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $businessStepFolder = $em->getRepository('AppMainBundle:BusinessStepFolder')->find($request->get("pk"));
            if ($businessStepFolder != null) {
                $businessStepFolder->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($businessStepFolder);
                if (count($errors) == 0) {
                    $em->persist($businessStepFolder);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "businessStepFolderId" => $businessStepFolder->getId(),
                                "entityType" => "folder"
                    )));
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
                $response = new Response("Unknown scenario folder", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/{id}/update/description",
     *      name="app_application_business_step_folder_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateBusinessStepFolderDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $businessStepFolder = $em->getRepository('AppMainBundle:BusinessStepFolder')->find($request->get("pk"));
            if ($businessStepFolder != null) {
                $businessStepFolder->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($businessStepFolder);
                if (count($errors) == 0) {
                    $em->persist($businessStepFolder);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "businessStepFolderId" => $businessStepFolder->getId(),
                                "entityType" => "folder"
                    )));
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
                $response = new Response("Unknown scenario folder", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/{id}/add/business/step",
     *      name="app_add_application_business_step_folder_business_step_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("businessStepFolder", class="AppMainBundle:BusinessStepFolder")
     */
    public function addBusinessStepAction(BusinessStepFolder $businessStepFolder, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $formBusinessStep = new BusinessStep();
            $formBusinessStep->setBusinessStepFolder($businessStepFolder);
            $formBusinessStep->setApplication($businessStepFolder->getRootApplication());
            $form = $this->createForm(new BusinessStepType(), $formBusinessStep);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $businessStep = $form->getData();
                $parameterSet = new ParameterSet();
                $businessStep->setParameterSet($parameterSet);
                $parameterSet->setBusinessStep($businessStep);

                $stepSentenceGroup = new StepSentenceGroup();
                $businessStep->setStepSentenceGroup($stepSentenceGroup);
                $stepSentenceGroup->setBusinessStep($businessStep);
                $locale = $this->container->getParameter('locale');
                $stepSentence = new StepSentence();
                $stepSentence->setLocale($locale);
                $stepSentence->setSentence($businessStep->generateSentence());
                $stepSentenceGroup->addSentence($stepSentence);

                $em->persist($businessStep);
                $em->flush();

                $businessStepFolder->setSelected(true);
                $ajaxResponse['id'] = $businessStep->getId();
                $ajaxResponse['name'] = $businessStep->getName();
                $ajaxResponse['description'] = $businessStep->getDescription();
                $application = $businessStepFolder->getRootApplication();
                $ajaxResponse['applicationId'] = $application->getId();
                $ajaxResponse['businessStepsCount'] = $application->getBusinessStepsCount();
                $ajaxResponse['treeBusinessSteps'] = $application->getJsonBusinessStepsTreeAsArray();
            } else {
                $ajaxResponse['error'] = Utility::getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/business/step/{id}/update/name",
     *      name="app_application_business_step_folder_business_step_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateBusinessStepNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $businessStep = $em->getRepository('AppMainBundle:BusinessStep')->find($request->get("pk"));
            if ($businessStep != null) {
                $businessStep->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($businessStep);
                if (count($errors) == 0) {
                    $em->persist($businessStep);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "businessStepId" => $businessStep->getId(),
                                "entityType" => "business-step"
                    )));
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
                $response = new Response("Unknown scenario", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/business/step/folder/business/step/{id}/update/description",
     *      name="app_application_business_step_folder_business_step_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateBusinessStepDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $businessStep = $em->getRepository('AppMainBundle:BusinessStep')->find($request->get("pk"));
            if ($businessStep != null) {
                $businessStep->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($businessStep);
                if (count($errors) == 0) {
                    $em->persist($businessStep);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "businessStepId" => $businessStep->getId(),
                                "entityType" => "business-step"
                    )));
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
                $response = new Response("Unknown scenario", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/business/step/entities/delete",
     *      name="app_application_business_step_entities_delete_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function deleteEntitiesAction(Application $application, Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $objects = $request->get("objects");
            $em = $this->getDoctrine()->getManager();
            foreach ($objects as $object) {
                $href = $object["href"];
                $id = substr($href, strrpos($href, "-") + 1);
                $type = substr($href, strpos($href, "-") + 1, strrpos($href, "-") - strpos($href, "-") - 1);
                if ($type == "folder") {
                    $persistedObject = $em->getRepository('AppMainBundle:BusinessStepFolder')->find($id);
                } elseif ($type == "business-step") {
                    $persistedObject = $em->getRepository('AppMainBundle:BusinessStep')->find($id);
                }
                $em->remove($persistedObject);
            }
            $em->flush();
            $application = $em->getRepository('AppMainBundle:Application')->find($application->getId());
            $ajaxResponse = array();
            $ajaxResponse['count'] = count($objects);
            $ajaxResponse['applicationId'] = $application->getId();
            $ajaxResponse['businessStepFoldersCount'] = $application->getBusinessStepFoldersCount();
            $ajaxResponse['businessStepsCount'] = $application->getBusinessStepsCount();
            $ajaxResponse['treeBusinessSteps'] = $application->getJsonBusinessStepsTreeAsArray();
            $response = new Response(json_encode($ajaxResponse));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
