<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\Test;
use App\MainBundle\Entity\TestFolder;
use App\MainBundle\Form\Type\TestFolderType;
use App\MainBundle\Form\Type\TestType;
use App\MainBundle\Utility;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestPlannerController extends BaseController {

    /**
     * @Route("/application/{id}/test/planner", name="app_index_application_test_planner")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function indexAction(Application $application) {
        $addTestFolderFormView = $this->createForm(new TestFolderType(), new TestFolder(), array(
                    'method' => 'POST'
                ))->createView();
        $addTestFormView = $this->createForm(new TestType(), new Test(), array(
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:test:planner/index.html.twig', array(
                    'application' => $application,
                    'addTestFolderFormView' => $addTestFolderFormView,
                    'addTestFormView' => $addTestFormView
        ));
    }

    /**
     * @Route("/application/{id}/tests/tree",
     *      name="app_application_get_tests_tree_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function getTestsTreeAction(Application $application, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["tree-tests-" . $application->getId()] = $application->getJsonTestsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/tests/tree/selected/folder/{id}",
     *      name="app_application_get_tests_tree_with_selected_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testFolder", class="AppMainBundle:TestFolder")
     */
    public function getTestsTreeWithSelectedFolderAction(TestFolder $testFolder, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $testFolder->setSelected(true);
            $application = $testFolder->getRootApplication();
            $ajaxResponse["tree-tests-" . $application->getId()] = $application->getJsonTestsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/tests/tree/selected/test/{id}",
     *      name="app_application_get_tests_tree_with_selected_test_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getTestsTreeWithSelectedTestAction(Test $test, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $test->setSelected(true);
            $application = $test->getTestFolder()->getRootApplication();
            $ajaxResponse["tree-tests-" . $application->getId()] = $application->getJsonTestsTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/add/test/folder/{parentId}",
     *      name="app_add_application_test_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true },
     *      defaults={"parentId" = -1}
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function addTestFolderAction(Application $application, $parentId, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $formTestFolder = new TestFolder();
            if ($parentId != -1) {
                $parentTestFolder = $em->getRepository("AppMainBundle:TestFolder")->find($parentId);
                $formTestFolder->setTestFolder($parentTestFolder);
            } else {
                $formTestFolder->setApplication($application);
            }
            $form = $this->createForm(new TestFolderType(), $formTestFolder);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $testFolder = $form->getData();
                $em->persist($testFolder);
                $testFolder->setSelected(true);
                $em->flush();
                $ajaxResponse['id'] = $testFolder->getId();
                $ajaxResponse['name'] = $testFolder->getName();
                $ajaxResponse['description'] = $testFolder->getDescription();
                $ajaxResponse['testFoldersCount'] = $application->getTestFoldersCount();
                $ajaxResponse['treeTests'] = $application->getJsonTestsTreeAsArray();
            } else {
                $ajaxResponse['error'] = Utility::getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/folder/{id}",
     *      name="app_application_get_test_folder_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testFolder", class="AppMainBundle:TestFolder")
     */
    public function getTestFolderAction(TestFolder $testFolder, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["testFolder"] = $testFolder;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/folder/test/{id}",
     *      name="app_application_get_test_folder_test_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("test", class="AppMainBundle:Test")
     */
    public function getTestAction(Test $test, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["test"] = $test;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/folder/{id}/update/name",
     *      name="app_application_test_folder_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateTestFolderNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $testFolder = $em->getRepository('AppMainBundle:TestFolder')->find($request->get("pk"));
            if ($testFolder != null) {
                $testFolder->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($testFolder);
                if (count($errors) == 0) {
                    $em->persist($testFolder);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testFolderId" => $testFolder->getId(),
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
     * @Route("/application/test/folder/{id}/update/description",
     *      name="app_application_test_folder_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateTestFolderDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $testFolder = $em->getRepository('AppMainBundle:TestFolder')->find($request->get("pk"));
            if ($testFolder != null) {
                $testFolder->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($testFolder);
                if (count($errors) == 0) {
                    $em->persist($testFolder);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testFolderId" => $testFolder->getId(),
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
     * @Route("/application/test/folder/{id}/add/test",
     *      name="app_add_application_test_folder_test_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testFolder", class="AppMainBundle:TestFolder")
     */
    public function addTestAction(TestFolder $testFolder, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $formTest = new Test();
            $formTest->setTestFolder($testFolder);
            $formTest->setApplication($testFolder->getRootApplication());
            $form = $this->createForm(new TestType(), $formTest);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $test = $form->getData();
                $em->persist($test);
                $testFolder->setSelected(true);
                $em->flush();
                $ajaxResponse['id'] = $test->getId();
                $ajaxResponse['name'] = $test->getName();
                $ajaxResponse['description'] = $test->getDescription();
                $application = $testFolder->getRootApplication();
                $ajaxResponse['applicationId'] = $application->getId();
                $ajaxResponse['testsCount'] = $application->getTestsCount();
                $ajaxResponse['treeTests'] = $application->getJsonTestsTreeAsArray();
            } else {
                $ajaxResponse['error'] = Utility::getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/folder/test/{id}/update/name",
     *      name="app_application_test_folder_test_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateTestNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $test = $em->getRepository('AppMainBundle:Test')->find($request->get("pk"));
            if ($test != null) {
                $test->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($test);
                if (count($errors) == 0) {
                    $em->persist($test);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testId" => $test->getId(),
                                "entityType" => "test"
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
     * @Route("/application/test/folder/test/{id}/update/description",
     *      name="app_application_test_folder_test_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateTestDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $test = $em->getRepository('AppMainBundle:Test')->find($request->get("pk"));
            if ($test != null) {
                $test->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($test);
                if (count($errors) == 0) {
                    $em->persist($test);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "testId" => $test->getId(),
                                "entityType" => "test"
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
     * @Route("/application/{id}/test/entities/delete",
     *      name="app_application_test_entities_delete_ajax",
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
                    $persistedObject = $em->getRepository('AppMainBundle:TestFolder')->find($id);
                } elseif ($type == "test") {
                    $persistedObject = $em->getRepository('AppMainBundle:Test')->find($id);
                }
                $em->remove($persistedObject);
            }
            $em->flush();
            $application = $em->getRepository('AppMainBundle:Application')->find($application->getId());
            $ajaxResponse = array();
            $ajaxResponse['count'] = count($objects);
            $ajaxResponse['applicationId'] = $application->getId();
            $ajaxResponse['testFoldersCount'] = $application->getTestFoldersCount();
            $ajaxResponse['testsCount'] = $application->getTestsCount();
            $ajaxResponse['treeTests'] = $application->getJsonTestsTreeAsArray();
            $response = new Response(json_encode($ajaxResponse));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
