<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\Project;
use App\MainBundle\Form\Type\ApplicationType;
use App\MainBundle\Form\Type\ProjectType;
use Doctrine\DBAL\DBALException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage", options={"expose" = true })
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $projects = $em->getRepository('AppMainBundle:Project')->findBy(
                array(), array('createdAt' => 'desc')
        );
        $addApplicationFormView = $this->createForm(new ApplicationType(), new Application(), array(
                    'action' => $this->generateUrl('app_add_application_ajax', array('id' => -1)),
                    'method' => 'POST'
                ))->createView();
        $addProjectFormView = $this->createForm(new ProjectType(), new Project(), array(
                    'action' => $this->generateUrl('app_add_project_ajax'),
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:default:index.html.twig', array(
                    'projects' => $projects,
                    'addApplicationFormView' => $addApplicationFormView,
                    'addProjectFormView' => $addProjectFormView
        ));
    }

    /**
     * @Route("/project/add",
     *      name="app_add_project_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function addProjectAction(Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ProjectType(), new Project());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $project = $form->getData();
                $em->persist($project);
                $em->flush();
                $ajaxResponse['project'] = $this->container->get('serializer')->serialize($project, 'json');
                $addApplicationFormView = $this->createForm(new ApplicationType(), new Application(), array(
                            'action' => $this->generateUrl('app_add_application_ajax', array('id' => -1)),
                            'method' => 'POST'
                        ))->createView();
                $ajaxResponse['panel'] = $this->render('AppMainBundle:project:item.html.twig', array(
                            'project' => $project,
                            'addApplicationFormView' => $addApplicationFormView
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
     * @Route("/project/{id}/delete",
     *      name="app_delete_project_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("project", class="AppMainBundle:Project")
     */
    public function deleteProjectAction(Project $project, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($project !== null) {
                $em->remove($project);
                $em->flush();
            } else {
                $ajaxResponse['error'] = "This project does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/project/{id}/application/add",
     *      name="app_add_application_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("project", class="AppMainBundle:Project")
     */
    public function addApplicationAction(Project $project, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($project !== null) {
                // recuperer les donnees du formulaire
                $form = $this->createForm(new ApplicationType(), new Application());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $application = $form->getData();
                    try {
                        $application->setProject($project);
                        $em->persist($application);
                        $em->flush();
                        $ajaxResponse['id'] = $application->getId();
                        $ajaxResponse['name'] = $application->getName();
                        $ajaxResponse['description'] = $application->getDescription();
                        $ajaxResponse['row'] = $this->render('AppMainBundle:application:item.html.twig', array(
                                    'project' => $project,
                                    'application' => $application
                                ))->getContent();
                    } catch (DBALException $e) {
                        $e->getCode();
                        if ($application->getName() == null || $application->getName() == "") {
                            $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                        } else {
                            $ajaxResponse['error'] = "ERROR: Name already used.";
                        }
                    }
                } else {
                    $ajaxResponse['error'] = (string) $form->getErrors(true);
                }
            } else {
                $ajaxResponse['error'] = "This project does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/project/{id}/application/delete",
     *      name="app_delete_application_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function deleteApplicationAction(Application $application, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($application !== null) {
                $em->remove($application);
                $em->flush();
            } else {
                $response['error'] = "This application does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
