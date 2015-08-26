<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\ExecutionServer;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExecutionServerController extends Controller {

    /**
     * @Route("/configuration/execution/server/{id}", name="app_index_configuration_execution_server")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("executionServer", class="AppMainBundle:ExecutionServer")
     */
    public function indexAction(ExecutionServer $executionServer) {
        return $this->render('AppMainBundle:configuration:execution-server/edit.html.twig', array(
                    'executionServer' => $executionServer
        ));
    }

    /**
     * @Route("/configuration/execution/server/{id}/update/name",
     *      name="app_configuration_execution_server_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $executionServer = $em->getRepository('AppMainBundle:ExecutionServer')->find($request->get("pk"));
            if ($executionServer != null) {
                $executionServer->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($executionServer);
                if (count($errors) == 0) {
                    $em->persist($executionServer);
                    $em->flush();
                    $name = "" . $executionServer;
                    $response = new Response(json_encode($name));
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
                $response = new Response("Unknown execution server", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/execution/server/{id}/update/description",
     *      name="app_configuration_execution_server_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $executionServer = $em->getRepository('AppMainBundle:ExecutionServer')->find($request->get("pk"));
            if ($executionServer != null) {
                $executionServer->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($executionServer);
                if (count($errors) == 0) {
                    $em->persist($executionServer);
                    $em->flush();
                    $response = new Response(json_encode($executionServer->getDescription()));
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
                $response = new Response("Unknown execution server", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/servers/all",
     *      name="app_get_configuration_servers_ajax",
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function getServersAction(Request $request) {
        $ajaxResponse = array();
        if (($request->getMethod() == 'GET' || $request->getMethod() == 'POST') && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $servers = $em->getRepository('AppMainBundle:Server')->findAll();
            foreach ($servers as $server) {
                $ajaxResponse[] = $server->getXEditableNode();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/execution/server/{id}/update/server",
     *      name="app_configuration_execution_server_update_server_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateServerAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $executionServer = $em->getRepository('AppMainBundle:ExecutionServer')->find($request->get("pk"));
            if ($executionServer != null) {
                $server = $em->getRepository('AppMainBundle:Server')->find($request->get("value"));
                if ($server != null) {
                    $executionServer->setServer($server);
                    $validator = $this->get('validator');
                    $errors = $validator->validate($executionServer);
                    if (count($errors) == 0) {
                        $em->persist($executionServer);
                        $em->flush();
                        $response = new Response(json_encode($executionServer->getServer() . ""));
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
                    $response = new Response("Unknown server", 400);
                }
            } else {
                $response = new Response("Unknown execution server", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/execution/server/{id}/update/art/runner/path",
     *      name="app_configuration_execution_server_update_art_runner_path_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateArtRunnerPathAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $executionServer = $em->getRepository('AppMainBundle:ExecutionServer')->find($request->get("pk"));
            if ($executionServer != null) {
                $executionServer->setArtRunnerPath($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($executionServer);
                if (count($errors) == 0) {
                    $em->persist($executionServer);
                    $em->flush();
                    $response = new Response(json_encode($executionServer->getArtRunnerPath()));
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
                $response = new Response("Unknown execution server", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
