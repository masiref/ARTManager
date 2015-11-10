<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\TestInstance;
use App\MainBundle\Entity\TestSet;
use App\MainBundle\Entity\TestSetRun;
use App\MainBundle\Form\Type\TestSetRunType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestSetEditorController extends BaseController {

    /**
     * @Route("/application/test/set/{id}/editor",
     *      name="app_index_application_test_set_editor",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet")
     */
    public function indexAction(TestSet $testSet) {
        $addTestSetRunFormView = $this->createForm(new TestSetRunType(), new TestSetRun(), array(
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:test-set:editor/index.html.twig', array(
                    'testSet' => $testSet,
                    'addTestSetRunFormView' => $addTestSetRunFormView
        ));
    }

    /**
     * @Route("/application/test/set/test/{id}/instances/add",
     *      name="app_add_application_test_set_instances_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet"))
     */
    public function addSelectedTestInstances(TestSet $testSet, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $objects = $request->get("objects");
            if ($objects !== null) {
                $count = 0;
                foreach ($objects as $object) {
                    $href = $object["href"];
                    $id = substr($href, strrpos($href, "-") + 1);
                    $type = substr($href, strpos($href, "-") + 1, strrpos($href, "-") - strpos($href, "-") - 1);
                    if ($type == "test") {
                        $count++;
                        $test = $em->getRepository('AppMainBundle:Test')->find($id);
                        $testInstance = new TestInstance();
                        $testInstance->setTest($test);
                        $testSet->addTestInstance($testInstance);
                    }
                }
                $em->persist($testSet);
                $em->flush();
                $ajaxResponse['count'] = $count;
                $ajaxResponse['executionGrid'] = $this->render('AppMainBundle:test-set:editor/execution-grid_content.html.twig', array(
                            'testSet' => $testSet
                        ))->getContent();
            } else {
                $ajaxResponse['error'] = "No scenario selected";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/test/instance/{id}/delete",
     *      name="app_delete_application_test_set_instance_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testInstance", class="AppMainBundle:TestInstance"))
     */
    public function deleteTestInstanceAction(TestInstance $testInstance, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $testSet = $testInstance->getTestSet();
            $order = $testInstance->getOrder();
            $em->remove($testInstance);
            $em->flush();
            foreach ($testSet->getTestInstances() as $testInstance) {
                if ($testInstance->getOrder() > $order) {
                    $testInstance->setOrder($testInstance->getOrder() - 1);
                }
            }
            $em->persist($testSet);
            $em->flush();
            $ajaxResponse['testSetId'] = $testSet->getId();
            $ajaxResponse['executionGrid'] = $this->render('AppMainBundle:test-set:editor/execution-grid_content.html.twig', array(
                        'testSet' => $testSet
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/test/instance/orders/update",
     *      name="app_update_application_test_set_test_instance_orders_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function updateTestInstancesOrdersAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $rowId = $request->get("id");
            $fromPosition = $request->get("fromPosition");
            $toPosition = $request->get("toPosition");
            $direction = $request->get("direction");
            $id = substr($rowId, strrpos($rowId, "-") + 1);
            $testInstance = $em->getRepository("AppMainBundle:TestInstance")->find($id);
            $testSet = $testInstance->getTestSet();
            foreach ($testSet->getTestInstances() as $testInstance1) {
                $order = $testInstance1->getOrder();
                if ($direction == "forward") {
                    if ($order > $fromPosition && $order <= $toPosition) {
                        $testInstance1->setOrder($order - 1);
                    }
                } else {
                    if ($order >= $toPosition && $order < $fromPosition) {
                        $testInstance1->setOrder($order + 1);
                    }
                }
            }
            $testInstance->setOrder($toPosition);
            $em->persist($testInstance);
            $em->persist($testSet);
            $em->flush();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/{id}/behat/feature",
     *      name="app_get_application_test_set_behat_feature_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet")
     */
    public function getBehatFeatureAction(TestSet $testSet, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["feature"] = $this->render('AppMainBundle:test-set:editor/behat_content.html.twig', array(
                        'testSet' => $testSet
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/{id}/run",
     *      name="app_application_test_set_run_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet")
     */
    public function runAction(TestSet $testSet, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new TestSetRunType(), new TestSetRun());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $testSetRun = $form->getData();
                $testSetRun->setUser($this->getUser());
                $testSet->addRun($testSetRun);
                $em->persist($testSet);
                $em->flush();
                $gearman = $this->get('gearman');
                $result = $gearman->doBackgroundJob('AppMainBundleServicesTestSetExecutionService~execute', json_encode(array(
                    'testSetId' => $testSet->getId(),
                    'executionServerId' => $testSetRun->getExecutionServer()->getId(),
                    'testSetRunId' => $testSetRun->getId(),
                    'testSetRunSlug' => $testSetRun->getSlug()
                )));
                $testSetRun->setStatus($em->getRepository("AppMainBundle:Status")->findDefaultTestSetRunStatus());
                $testSetRun->setGearmanJobHandle($result);
                $em->persist($testSetRun);
                $em->flush();
                $ajaxResponse['executionGrid'] = $this->render('AppMainBundle:test-set:editor/execution-grid_content.html.twig', array(
                            'testSet' => $testSet
                        ))->getContent();
                $ajaxResponse['historyGrid'] = $this->render('AppMainBundle:test-set:run/history-grid_table.html.twig', array(
                            'testSet' => $testSet
                        ))->getContent();
            } else {
                $ajaxResponse['error'] = $this->getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/sets/run",
     *      name="app_application_test_sets_run_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     */
    public function multipleRunAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $objects = json_decode($request->get("objects"), true);
            $request->query->remove("objects");
            $form = $this->createForm(new TestSetRunType(), new TestSetRun());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $executionServer = $form->getData()->getExecutionServer();
                foreach ($objects as $object) {
                    $href = $object["href"];
                    $id = substr($href, strrpos($href, "-") + 1);
                    $type = substr($href, strpos($href, "-") + 1, strrpos($href, "-") - strpos($href, "-") - 1);
                    if ($type == "test-set") {
                        $newTestSetRun = new TestSetRun();
                        $newTestSetRun->setExecutionServer($executionServer);
                        $newTestSetRun->setUser($this->getUser());
                        $testSet = $em->getRepository('AppMainBundle:TestSet')->find($id);
                        $testSet->addRun($newTestSetRun);
                        $em->persist($testSet);
                        $em->flush();
                        $gearman = $this->get('gearman');
                        $result = $gearman->doBackgroundJob('AppMainBundleServicesTestSetExecutionService~execute', json_encode(array(
                            'testSetId' => $testSet->getId(),
                            'executionServerId' => $newTestSetRun->getExecutionServer()->getId(),
                            'testSetRunId' => $newTestSetRun->getId(),
                            'testSetRunSlug' => $newTestSetRun->getSlug()
                        )));
                        $newTestSetRun->setStatus($em->getRepository("AppMainBundle:Status")->findDefaultTestSetRunStatus());
                        $newTestSetRun->setGearmanJobHandle($result);
                        $em->persist($newTestSetRun);
                        $em->flush();
                    }
                }
            } else {
                $ajaxResponse['error'] = $this->getErrorsAsString($form);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/{id}/execution/grid",
     *      name="app_get_application_test_set_execution_grid_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSet", class="AppMainBundle:TestSet")
     */
    public function getExecutionGridAction(TestSet $testSet, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse['executionGrid'] = $this->render('AppMainBundle:test-set:editor/execution-grid_content.html.twig', array(
                        'testSet' => $testSet
                    ))->getContent();
            $ajaxResponse['historyGrid'] = $this->render('AppMainBundle:test-set:run/history-grid_table.html.twig', array(
                        'testSet' => $testSet
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/run/{id}/execution/grid",
     *      name="app_get_application_test_set_run_execution_grid_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSetRun", class="AppMainBundle:TestSetRun")
     */
    public function getRunExecutionGridAction(TestSetRun $testSetRun, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse['executionGrid'] = $this->render('AppMainBundle:test-set:run/execution-grid_table.html.twig', array(
                        'testSetRun' => $testSetRun
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/test/set/run/{id}/execution/report",
     *      name="app_get_application_test_set_run_execution_report_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("testSetRun", class="AppMainBundle:TestSetRun")
     */
    public function getRunExecutionReportAction(TestSetRun $testSetRun, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse['report'] = $this->render('AppMainBundle:test-set:run/report_content.html.twig', array(
                        'executionReport' => $testSetRun->getExecutionReport()
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
