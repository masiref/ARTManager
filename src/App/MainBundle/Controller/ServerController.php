<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Server;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends BaseController {

    /**
     * @Route("/configuration/server/{id}", name="app_index_configuration_server")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("server", class="AppMainBundle:Server")
     */
    public function indexAction(Server $server) {
        return $this->render('AppMainBundle:configuration:server/edit.html.twig', array(
                    'server' => $server
        ));
    }

    /**
     * @Route("/configuration/server/{id}/update/name",
     *      name="app_configuration_server_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $server = $em->getRepository('AppMainBundle:Server')->find($request->get("pk"));
            if ($server != null) {
                $server->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($server);
                if (count($errors) == 0) {
                    $em->persist($server);
                    $em->flush();
                    $name = "" . $server;
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
                $response = new Response("Unknown server", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/update/description",
     *      name="app_configuration_server_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $server = $em->getRepository('AppMainBundle:Server')->find($request->get("pk"));
            if ($server != null) {
                $server->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($server);
                if (count($errors) == 0) {
                    $em->persist($server);
                    $em->flush();
                    $response = new Response(json_encode($server->getDescription()));
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
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/update/host",
     *      name="app_configuration_server_update_host_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateHostAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $server = $em->getRepository('AppMainBundle:Server')->find($request->get("pk"));
            if ($server != null) {
                $server->setHost($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($server);
                if (count($errors) == 0) {
                    $em->persist($server);
                    $em->flush();
                    $response = new Response(json_encode($server->getHost()));
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
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/update/port",
     *      name="app_configuration_server_update_port_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updatePortAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $server = $em->getRepository('AppMainBundle:Server')->find($request->get("pk"));
            if ($server != null) {
                $server->setPort($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($server);
                if (count($errors) == 0) {
                    $em->persist($server);
                    $em->flush();
                    $response = new Response(json_encode($server->getPort()));
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
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/update/username",
     *      name="app_configuration_server_update_username_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateUsernameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $server = $em->getRepository('AppMainBundle:Server')->find($request->get("pk"));
            if ($server != null) {
                $server->setUsername($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($server);
                if (count($errors) == 0) {
                    $em->persist($server);
                    $em->flush();
                    $response = new Response(json_encode($server->getUsername()));
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
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/update/password",
     *      name="app_configuration_server_update_password_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updatePasswordAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $server = $em->getRepository('AppMainBundle:Server')->find($request->get("pk"));
            if ($server != null) {
                $server->setPassword($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($server);
                if (count($errors) == 0) {
                    $em->persist($server);
                    $em->flush();
                    $response = new Response(json_encode($server->getPassword()));
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
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/configuration/server/{id}/check/connection",
     *      name="app_index_configuration_server_check_connection_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("server", class="AppMainBundle:Server")
     */
    public function checkConnectionAction(Server $server, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            /* $gearman = $this->get('gearman');
              $result = $gearman->doBackgroundJob('AppMainBundleWorkersTestSetExecutionWorker~testA', json_encode(array('value1')));
              $ajaxResponse['result'] = $gearman->getJobStatus($result)->isFinished(); */
            $ajaxResponse['result'] = $server->checkConnection() ? "Successfully connected !" : "Connection failed !";
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
