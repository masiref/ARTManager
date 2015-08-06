<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\Test;
use App\MainBundle\Entity\TestFolder;
use App\MainBundle\Form\Type\TestFolderType;
use App\MainBundle\Form\Type\TestType;
use Doctrine\DBAL\DBALException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestPlannerController extends Controller {

    /**
     * @Route("/application/{id}/test/planner", name="app_index_application_test_planner")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function indexAction(Application $application) {
        $addTestFolderFormView = $this->createForm(new TestFolderType(), new TestFolder(), array(
                    'action' => $this->generateUrl('app_add_application_test_folder_ajax', array('id' => -1, 'parentId' => -1)),
                    'method' => 'POST'
                ))->createView();
        $addTestFormView = $this->createForm(new TestType(), new Test(), array(
                    //'action' => $this->generateUrl('app_add_application_test_ajax', array('id' => -1, 'parentId' => -1)),
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function addTestFolderAction($application, $parentId, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($application !== null) {
                $form = $this->createForm(new TestFolderType(), new TestFolder());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $testFolder = $form->getData();
                    try {
                        if ($parentId != -1) {
                            $parentTestFolder = $em->getRepository("AppMainBundle:TestFolder")->find($parentId);
                            $parentTestFolder->addTestFolder($testFolder);
                            $em->persist($parentTestFolder);
                        } else {
                            $application->addTestFolder($testFolder);
                            $em->persist($application);
                        }
                        $testFolder->setSelected(true);
                        $em->flush();
                        $ajaxResponse['id'] = $testFolder->getId();
                        $ajaxResponse['name'] = $testFolder->getName();
                        $ajaxResponse['description'] = $testFolder->getDescription();
                        $ajaxResponse['testFoldersCount'] = $application->getTestFoldersCount();
                        $ajaxResponse['treeTests'] = $application->getJsonTestsTreeAsArray();
                    } catch (DBALException $e) {
                        $e->getCode();
                        if ($testFolder->getName() == null || $testFolder->getName() == "") {
                            $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                        } else {
                            $ajaxResponse['error'] = "ERROR: Name already used.";
                        }
                    }
                } else {
                    $ajaxResponse['error'] = (string) $form->getErrors(true);
                }
            } else {
                $ajaxResponse['error'] = "This application does not exist.";
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
                $response = new Response("Unknown test folder", 400);
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
                $response = new Response("Unknown test folder", 400);
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("testFolder", class="AppMainBundle:TestFolder")
     */
    public function addTestAction($testFolder, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($testFolder !== null) {
                $form = $this->createForm(new TestType(), new Test());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $test = $form->getData();
                    try {
                        $testFolder->addTest($test);
                        $em->persist($testFolder);
                        $testFolder->setSelected(true);
                        $em->flush();
                        $ajaxResponse['id'] = $test->getId();
                        $ajaxResponse['name'] = $test->getName();
                        $ajaxResponse['description'] = $test->getDescription();
                        $application = $test->getTestFolder()->getRootApplication();
                        $ajaxResponse['applicationId'] = $application->getId();
                        $ajaxResponse['testsCount'] = $application->getTestsCount();
                        $ajaxResponse['treeTests'] = $application->getJsonTestsTreeAsArray();
                    } catch (DBALException $e) {
                        $e->getCode();
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
                $ajaxResponse['error'] = "This test folder does not exist.";
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
                $response = new Response("Unknown test", 400);
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
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
                $response = new Response("Unknown test", 400);
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function deleteEntitiesAction($application, Request $request) {
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
