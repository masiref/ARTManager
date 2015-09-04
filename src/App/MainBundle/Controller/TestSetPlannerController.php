<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\TestSet;
use App\MainBundle\Entity\TestSetFolder;
use App\MainBundle\Form\Type\TestSetFolderType;
use App\MainBundle\Form\Type\TestSetType;
use Doctrine\DBAL\DBALException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestSetPlannerController extends BaseController {

    /**
     * @Route("/application/{id}/test/set/planner", name="app_index_application_test_set_planner")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function indexAction(Application $application) {
        $addTestSetFolderFormView = $this->createForm(new TestSetFolderType(), new TestSetFolder(), array(
                    'method' => 'POST'
                ))->createView();
        $addTestSetFormView = $this->createForm(new TestSetType(), new TestSet(), array(
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:test-set:planner/index.html.twig', array(
                    'application' => $application,
                    'addTestSetFolderFormView' => $addTestSetFolderFormView,
                    'addTestSetFormView' => $addTestSetFormView
        ));
    }

    /**
     * @Route("/application/{id}/test/sets/tree",
     *      name="app_application_get_test_sets_tree_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function getTestSetsTreeAction(Application $application, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["tree-test-sets-" . $application->getId()] = $application->getJsonTestSetsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/sets/tree/selected/folder/{id}",
     *      name="app_application_get_test_sets_tree_with_selected_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("testSetFolder", class="AppMainBundle:TestSetFolder")
     */
    public function getTestSetsTreeWithSelectedFolderAction(TestSetFolder $testSetFolder, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $testSetFolder->setSelected(true);
            $application = $testSetFolder->getRootApplication();
            $ajaxResponse["tree-test-sets-" . $application->getId()] = $application->getJsonTestSetsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/sets/tree/selected/test/set/{id}",
     *      name="app_application_get_test_sets_tree_with_selected_test_set_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet")
     */
    public function getTestSetsTreeWithSelectedTestSetAction(TestSet $testSet, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $testSet->setSelected(true);
            $application = $testSet->getTestSetFolder()->getRootApplication();
            $ajaxResponse["tree-test-sets-" . $application->getId()] = $application->getJsonTestSetsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/add/test/set/folder/{parentId}",
     *      name="app_add_application_test_set_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true },
     *      defaults={"parentId" = -1}
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function addTestSetFolderAction(Application $application, $parentId, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new TestSetFolderType(), new TestSetFolder());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $testSetFolder = $form->getData();
                try {
                    if ($parentId != -1) {
                        $parentTestSetFolder = $em->getRepository("AppMainBundle:TestSetFolder")->find($parentId);
                        $parentTestSetFolder->addTestSetFolder($testSetFolder);
                        $em->persist($parentTestSetFolder);
                    } else {
                        $application->addTestSetFolder($testSetFolder);
                        $em->persist($application);
                    }
                    $testSetFolder->setSelected(true);
                    $em->flush();
                    $ajaxResponse['id'] = $testSetFolder->getId();
                    $ajaxResponse['name'] = $testSetFolder->getName();
                    $ajaxResponse['description'] = $testSetFolder->getDescription();
                    $ajaxResponse['testSetFoldersCount'] = $application->getTestSetFoldersCount();
                    $ajaxResponse['treeTestSets'] = $application->getJsonTestSetsTreeAsArray();
                } catch (DBALException $e) {
                    $e->getCode();
                    if ($testSetFolder->getName() == null || $testSetFolder->getName() == "") {
                        $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                    } else {
                        $ajaxResponse['error'] = "ERROR: Name already used.";
                    }
                }
            } else {
                $ajaxResponse['error'] = (string) $form->getErrors(true);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/{id}",
     *      name="app_application_get_test_set_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("testSetFolder", class="AppMainBundle:TestSetFolder")
     */
    public function getTestSetFolderAction(TestSetFolder $testSetFolder, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["testSetFolder"] = $testSetFolder;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/test/set/{id}",
     *      name="app_application_get_test_set_folder_test_set_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet")
     */
    public function getTestSetAction(TestSet $testSet, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["testSet"] = $testSet;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/{id}/update/name",
     *      name="app_application_test_set_folder_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateTestSetFolderNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $testSetFolder = $em->getRepository('AppMainBundle:TestSetFolder')->find($request->get("pk"));
            if ($testSetFolder != null) {
                $testSetFolder->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($testSetFolder);
                if (count($errors) == 0) {
                    $em->persist($testSetFolder);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testSetFolderId" => $testSetFolder->getId(),
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
                $response = new Response("Unknown test set folder", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/{id}/update/description",
     *      name="app_application_test_set_folder_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateTestSetFolderDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $testSetFolder = $em->getRepository('AppMainBundle:TestSetFolder')->find($request->get("pk"));
            if ($testSetFolder != null) {
                $testSetFolder->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($testSetFolder);
                if (count($errors) == 0) {
                    $em->persist($testSetFolder);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testSetFolderId" => $testSetFolder->getId(),
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
                $response = new Response("Unknown test set folder", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/{id}/add/test/set",
     *      name="app_add_application_test_set_folder_test_set_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("testSetFolder", class="AppMainBundle:TestSetFolder")
     */
    public function addTestSetAction(TestSetFolder $testSetFolder, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new TestSetType(), new TestSet());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $testSet = $form->getData();
                try {
                    $testSetFolder->addTestSet($testSet);
                    $em->persist($testSetFolder);
                    $testSetFolder->setSelected(true);
                    $em->flush();
                    $ajaxResponse['id'] = $testSet->getId();
                    $ajaxResponse['name'] = $testSet->getName();
                    $ajaxResponse['description'] = $testSet->getDescription();
                    $application = $testSet->getTestSetFolder()->getRootApplication();
                    $ajaxResponse['applicationId'] = $application->getId();
                    $ajaxResponse['testSetsCount'] = $application->getTestSetsCount();
                    $ajaxResponse['treeTestSets'] = $application->getJsonTestSetsTreeAsArray();
                } catch (DBALException $e) {
                    $e->getCode();
                    if ($testSet->getName() == null || $testSet->getName() == "") {
                        $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                    } else {
                        $ajaxResponse['error'] = "ERROR: Name already used.";
                    }
                }
            } else {
                $ajaxResponse['error'] = (string) $form->getErrors(true);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/test/set/{id}/update/name",
     *      name="app_application_test_set_folder_test_set_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateTestSetNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $testSet = $em->getRepository('AppMainBundle:TestSet')->find($request->get("pk"));
            if ($testSet != null) {
                $testSet->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($testSet);
                if (count($errors) == 0) {
                    $em->persist($testSet);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testSetId" => $testSet->getId(),
                                "entityType" => "test-set"
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
                $response = new Response("Unknown test set", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/folder/test/set/{id}/update/description",
     *      name="app_application_test_set_folder_test_set_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateTestSetDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $testSet = $em->getRepository('AppMainBundle:TestSet')->find($request->get("pk"));
            if ($testSet != null) {
                $testSet->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($testSet);
                if (count($errors) == 0) {
                    $em->persist($testSet);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testSetId" => $testSet->getId(),
                                "entityType" => "test-set"
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
                $response = new Response("Unknown test set", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/test/set/entities/delete",
     *      name="app_application_test_set_entities_delete_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
                    $persistedObject = $em->getRepository('AppMainBundle:TestSetFolder')->find($id);
                } elseif ($type == "test-set") {
                    $persistedObject = $em->getRepository('AppMainBundle:TestSet')->find($id);
                }
                $em->remove($persistedObject);
            }
            $em->flush();
            $application = $em->getRepository('AppMainBundle:Application')->find($application->getId());
            $ajaxResponse = array();
            $ajaxResponse['count'] = count($objects);
            $ajaxResponse['applicationId'] = $application->getId();
            $ajaxResponse['testSetFoldersCount'] = $application->getTestSetFoldersCount();
            $ajaxResponse['testSetsCount'] = $application->getTestSetsCount();
            $ajaxResponse['treeTestSets'] = $application->getJsonTestSetsTreeAsArray();
            $response = new Response(json_encode($ajaxResponse));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
