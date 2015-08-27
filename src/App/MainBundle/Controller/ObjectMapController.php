<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Application;
use App\MainBundle\Entity\ObjectMap;
use App\MainBundle\Form\Type\ObjectMapType;
use Doctrine\DBAL\DBALException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ObjectMapController extends Controller {

    /**
     * @Route("/application/{id}/object/map", name="app_index_application_object_map")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function indexAction(Application $application) {
        $em = $this->getDoctrine()->getManager();
        $addObjectMapFormView = $this->createForm(new ObjectMapType(), new ObjectMap(), array(
                    'action' => $this->generateUrl('app_add_application_object_map_ajax', array('id' => -1)),
                    'method' => 'POST'
                ))->createView();
        $objectTypes = $em->getRepository('AppMainBundle:ObjectType')->findAll();
        return $this->render('AppMainBundle:object-map:index.html.twig', array(
                    'application' => $application,
                    'objectMaps' => $application->getObjectMaps(),
                    'addObjectMapFormView' => $addObjectMapFormView,
                    'objectTypes' => $objectTypes
        ));
    }

    /**
     * @Route("/application/{id}/object/map/tree",
     *      name="app_application_get_object_map_tree_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("objectMap", class="AppMainBundle:ObjectMap")
     */
    public function getObjectMapTreeAction(ObjectMap $objectMap, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["tree-object-map-" . $objectMap->getId()] = $objectMap->getJsonTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/object/map/trees",
     *      name="app_application_get_object_map_trees_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function getObjectMapTreesAction(Application $application, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            foreach ($application->getObjectMaps() as $objectMap) {
                $ajaxResponse["tree-object-map-" . $objectMap->getId()] = $objectMap->getJsonTreeAsArray();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/object/map/add",
     *      name="app_add_application_object_map_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("application", class="AppMainBundle:Application")
     */
    public function addObjectMapAction(Application $application, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $form = $this->createForm(new ObjectMapType(), new ObjectMap());
            $form->handleRequest($request);
            if ($form->isValid()) {
                $objectMap = $form->getData();
                try {
                    $objectMap->setApplication($application);
                    $em->persist($objectMap);
                    $em->flush();
                    $ajaxResponse['objectMap'] = $objectMap;
                    $addObjectMapFormView = $this->createForm(new ObjectMapType(), new ObjectMap(), array(
                                'action' => $this->generateUrl('app_add_application_object_map_ajax', array('id' => -1)),
                                'method' => 'POST'
                            ))->createView();
                    $ajaxResponse['panel'] = $this->render('AppMainBundle:object-map:item.html.twig', array(
                                'application' => $application,
                                'objectMap' => $objectMap,
                                'addObjectMapFormView' => $addObjectMapFormView
                            ))->getContent();
                } catch (DBALException $e) {
                    $e->getCode();
                    if ($objectMap->getName() == null || $objectMap->getName() == "") {
                        $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                    } else {
                        $ajaxResponse['error'] = "ERROR: Name already used.";
                    }
                }
            } else {
                $ajaxResponse['error'] = (string) $form->getErrors(true);
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/{id}/object/map/delete",
     *      name="app_delete_application_object_map_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("objectMap", class="AppMainBundle:ObjectMap")
     */
    public function deleteObjectMapAction(ObjectMap $objectMap, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($objectMap !== null) {
                $em->remove($objectMap);
                $em->flush();
            } else {
                $response['error'] = "This object map does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
