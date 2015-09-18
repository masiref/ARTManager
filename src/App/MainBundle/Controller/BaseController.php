<?php

namespace App\MainBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends Controller {

    public function render($view, array $parameters = array(), Response $response = null) {
        $em = $this->getDoctrine()->getManager();
        $plannedTestSetRuns = $em->getRepository("AppMainBundle:TestSetRun")->findPlannedOrderByCreatedAt();
        $parameters['plannedTestSetRuns'] = array(
            "count" => count($plannedTestSetRuns),
            "byApplicationTestSet" => $this->getTestSetRunsByApplicationTestSet($plannedTestSetRuns)
        );
        $recentTestSetRuns = $em->getRepository("AppMainBundle:TestSetRun")->findRecentOrderByCreatedAt();
        $parameters['recentTestSetRuns'] = array(
            "count" => count($recentTestSetRuns),
            "byApplicationTestSet" => $this->getTestSetRunsByApplicationTestSet($recentTestSetRuns)
        );
        return parent::render($view, $parameters, $response);
    }

    private function getTestSetRunsByApplicationTestSet($testSetRuns) {
        $result = array();
        foreach ($testSetRuns as $testSetRun) {
            $testSet = $testSetRun->getTestSet();
            $application = $testSet->getApplication();
            if (!array_key_exists($application->getId(), $result)) {
                $result[$application->getId()] = array(
                    "application" => $application,
                    "testSets" => array()
                );
            }
            if (!array_key_exists($testSet->getId(), $result[$application->getId()]["testSets"])) {
                $result[$application->getId()]["testSets"][$testSet->getId()] = array(
                    "testSet" => $testSet,
                    "testSetRuns" => array()
                );
            }
            $result[$application->getId()]["testSets"][$testSet->getId()]["testSetRuns"][] = $testSetRun;
        }
        return $result;
    }

    /**
     * @Route("/application/sidebar/refresh",
     *      name="application_refresh_sidebar",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function refreshSidebar(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $plannedTestSetRuns = $em->getRepository("AppMainBundle:TestSetRun")->findPlannedOrderByCreatedAt();
            $recentTestSetRuns = $em->getRepository("AppMainBundle:TestSetRun")->findRecentOrderByCreatedAt();
            $ajaxResponse['sidebar'] = $this->render('AppMainBundle:test-set:run/section.html.twig', array(
                        'title' => "Active",
                        'icon' => "flash",
                        'context' => "success",
                        'count' => count($plannedTestSetRuns),
                        'testSetRunsByApplicationTestSet' => $this->getTestSetRunsByApplicationTestSet($plannedTestSetRuns)
                    ))->getContent();
            $ajaxResponse['sidebar'] .= $this->render('AppMainBundle:test-set:run/section.html.twig', array(
                        'title' => "Recent",
                        'icon' => "back-in-time",
                        'context' => "info",
                        'count' => count($recentTestSetRuns),
                        'testSetRunsByApplicationTestSet' => $this->getTestSetRunsByApplicationTestSet($recentTestSetRuns)
                    ))->getContent();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function getErrorsAsString(Form $form) {
        $errors = "";
        $iterator = $form->getErrors(true);
        while ($iterator->current() != null) {
            $errors .= $iterator->current()->getMessage() . "\n";
            $iterator->next();
        }
        return $errors;
    }

}
