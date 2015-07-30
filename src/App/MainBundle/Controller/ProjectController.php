<?php

namespace App\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\MainBundle\Entity\Project;
use App\MainBundle\Form\Type\ApplicationType;
use App\MainBundle\Entity\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller {

    /**
     * @Route("/project/{id}", name="app_index_project")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("project", class="AppMainBundle:Project")
     */
    public function indexAction(Project $project) {
        $addApplicationFormView = $this->createForm(new ApplicationType(), new Application(), array(
                    'action' => $this->generateUrl('app_add_application_ajax', array('id' => -1)),
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:project:index.html.twig', array(
                    'project' => $project,
                    'addApplicationFormView' => $addApplicationFormView
        ));
    }

    /**
     * @Route("/project/{id}/update/name", name="app_project_update_name_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $project = $em->getRepository('AppMainBundle:Project')->find($request->get("pk"));
            if ($project != null) {
                $project->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($project);
                if (count($errors) == 0) {
                    $em->persist($project);
                    $em->flush();
                    $response = new Response(json_encode($project->getName()));
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
     * @Route("/project/{id}/update/description", name="app_project_update_description_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $project = $em->getRepository('AppMainBundle:Project')->find($request->get("pk"));
            if ($project != null) {
                $project->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($project);
                if (count($errors) == 0) {
                    $em->persist($project);
                    $em->flush();
                    $response = new Response(json_encode($project->getDescription()));
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
