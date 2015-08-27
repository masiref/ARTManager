<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\ExecutionServer;
use App\MainBundle\Entity\Server;
use App\MainBundle\Form\Type\ExecutionServerType;
use App\MainBundle\Form\Type\ServerType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends Controller {

    /**
     * @Route("/configuration", name="configuration", options={"expose" = true })
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $servers = $em->getRepository('AppMainBundle:Server')->findBy(
                array(), array('createdAt' => 'desc')
        );
        $executionServers = $em->getRepository('AppMainBundle:ExecutionServer')->findBy(
                array(), array('createdAt' => 'desc')
        );
        return $this->render('AppMainBundle:configuration:index.html.twig', array(
                    "servers" => $servers,
                    "executionServers" => $executionServers
        ));
    }

    /**
     * @Route("/configuration/servers",
     *      name="app_configuration_servers",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function serversAction() {
        $em = $this->getDoctrine()->getManager();
        $servers = $em->getRepository('AppMainBundle:Server')->findBy(
                array(), array('createdAt' => 'desc')
        );
        $addServerFormView = $this->createForm(new ServerType(), new Server(), array(
                    'action' => $this->generateUrl('app_add_configuration_server_ajax'),
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:configuration:server/index.html.twig', array(
                    "servers" => $servers,
                    "addServerFormView" => $addServerFormView
        ));
    }

    /**
     * @Route("/configuration/server/add",
     *      name="app_add_configuration_server_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function addServerAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ServerType(), new Server());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $server = $form->getData();
                $em->persist($server);
                $em->flush();
                $ajaxResponse['server'] = $server;
                $ajaxResponse['panel'] = $this->render('AppMainBundle:configuration:server/item.html.twig', array(
                            'server' => $server
                        ))->getContent();
            } else {
                $ajaxResponse['error'] = (string) $form->getErrors(true);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/delete",
     *      name="app_delete_configuration_server_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("server", class="AppMainBundle:Server")
     */
    public function deleteServerAction(Server $server, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($server !== null) {
                $em->remove($server);
                $em->flush();
            } else {
                $ajaxResponse['error'] = "This server does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/execution/servers",
     *      name="app_configuration_execution_servers",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function executionServersAction() {
        $em = $this->getDoctrine()->getManager();
        $executionServers = $em->getRepository('AppMainBundle:ExecutionServer')->findBy(
                array(), array('createdAt' => 'desc')
        );
        $addExecutionServerFormView = $this->createForm(new ExecutionServerType(), new ExecutionServer(), array(
                    'action' => $this->generateUrl('app_add_configuration_execution_server_ajax'),
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:configuration:execution-server/index.html.twig', array(
                    "executionServers" => $executionServers,
                    'addExecutionServerFormView' => $addExecutionServerFormView
        ));
    }

    /**
     * @Route("/configuration/execution/server/add",
     *      name="app_add_configuration_execution_server_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function addExecutionServerAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ExecutionServerType(), new ExecutionServer());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $executionServer = $form->getData();
                $em->persist($executionServer);
                $em->flush();
                $ajaxResponse['executionServer'] = $executionServer;
                $ajaxResponse['panel'] = $this->render('AppMainBundle:configuration:execution-server/item.html.twig', array(
                            'executionServer' => $executionServer
                        ))->getContent();
            } else {
                $ajaxResponse['error'] = (string) $form->getErrors(true);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/execution/server/{id}/delete",
     *      name="app_delete_configuration_execution_server_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("executionServer", class="AppMainBundle:ExecutionServer")
     */
    public function deleteExecutionServerAction(ExecutionServer $executionServer, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($executionServer !== null) {
                $em->remove($executionServer);
                $em->flush();
            } else {
                $ajaxResponse['error'] = "This execution server does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}