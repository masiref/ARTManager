<?php

namespace App\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\MainBundle\Entity\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends BaseController {

    /**
     * @Route("/application/{id}", name="app_index_application")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function indexAction(Application $application) {
        return $this->render('AppMainBundle:application:index.html.twig', array(
                    'application' => $application
        ));
    }

    /**
     * @Route("/application/{id}/update/name",
     *      name="app_application_update_name_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $application = $em->getRepository('AppMainBundle:Application')->find($request->get("pk"));
            if ($application != null) {
                $application->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($application);
                if (count($errors) == 0) {
                    $em->persist($application);
                    $em->flush();
                    $name = "" . $application;
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
                $response = new Response("Unknown project", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/update/description",
     *      name="app_application_update_description_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $application = $em->getRepository('AppMainBundle:Application')->find($request->get("pk"));
            if ($application != null) {
                $application->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($application);
                if (count($errors) == 0) {
                    $em->persist($application);
                    $em->flush();
                    $response = new Response(json_encode($application->getDescription()));
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
                $response = new Response("Unknown project", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/update/url",
     *      name="app_application_update_url_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateUrlAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $application = $em->getRepository('AppMainBundle:Application')->find($request->get("pk"));
            if ($application != null) {
                $application->setUrl($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($application);
                if (count($errors) == 0) {
                    $em->persist($application);
                    $em->flush();
                    $response = new Response(json_encode($application->getUrl()));
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
                $response = new Response("Unknown project", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
