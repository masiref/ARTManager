<?php

namespace App\MainBundle\Controller;

use App\MainBundle\Entity\Object;
use App\MainBundle\Entity\ObjectIdentifier;
use App\MainBundle\Entity\ObjectMap;
use App\MainBundle\Entity\Page;
use App\MainBundle\Form\Type\ObjectMapType;
use App\MainBundle\Form\Type\ObjectType;
use App\MainBundle\Form\Type\PageType;
use Doctrine\DBAL\DBALException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ObjectMapEditorController extends Controller {

    /**
     * @Route("/application/object/map/{id}/editor", name="app_editor_application_object_map")
     * @Secure(roles="ROLE_USER")
     * @ParamConverter("objectMap", class="AppMainBundle:ObjectMap")
     */
    public function editorAction(ObjectMap $objectMap) {
        $addObjectMapFormView = $this->createForm(new ObjectMapType(), new ObjectMap(), array(
                    'action' => $this->generateUrl('app_add_application_object_map_ajax', array('id' => -1)),
                    'method' => 'POST'
                ))->createView();
        $addPageFormView = $this->createForm(new PageType(), new Page(), array(
                    'action' => $this->generateUrl('app_add_application_object_map_page_ajax', array('id' => -1, 'parentId' => -1)),
                    'method' => 'POST'
                ))->createView();
        $addObjectFormView = $this->createForm(new ObjectType(), new Object(), array(
                    //'action' => $this->generateUrl('app_add_application_object_map_page_ajax', array('id' => -1, 'parentId' => -1)),
                    'method' => 'POST'
                ))->createView();
        return $this->render('AppMainBundle:object-map:editor/index.html.twig', array(
                    'application' => $objectMap->getApplication(),
                    'objectMap' => $objectMap,
                    'addObjectMapFormView' => $addObjectMapFormView,
                    'addPageFormView' => $addPageFormView,
                    'addObjectFormView' => $addObjectFormView
        ));
    }

    /**
     * @Route("/application/object/map/{id}/update/name", name="app_application_object_map_update_name_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $objectMap = $em->getRepository('AppMainBundle:ObjectMap')->find($request->get("pk"));
            if ($objectMap != null) {
                $objectMap->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($objectMap);
                if (count($errors) == 0) {
                    $em->persist($objectMap);
                    $em->flush();
                    $response = new Response(json_encode($objectMap->getName()));
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
                $response = new Response("Unknown object map", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/{id}/update/description", name="app_application_object_map_update_description_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $objectMap = $em->getRepository('AppMainBundle:ObjectMap')->find($request->get("pk"));
            if ($objectMap != null) {
                $objectMap->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($objectMap);
                if (count($errors) == 0) {
                    $em->persist($objectMap);
                    $em->flush();
                    $response = new Response(json_encode($objectMap->getDescription()));
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
                $response = new Response("Unknown object map", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/{id}", name="app_application_get_object_map_page_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("page", class="AppMainBundle:Page")
     */
    public function getPageAction(Page $page, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["page"] = $page;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/{id}/add/page/{parentId}",
     *          name="app_add_application_object_map_page_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true },
     *          defaults={"parentId" = -1}
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("objectMap", class="AppMainBundle:ObjectMap")
     */
    public function addPageAction($objectMap, $parentId, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($objectMap !== null) {
                $form = $this->createForm(new PageType(), new Page());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {
                        $page = $form->getData();
                        $page->setObjectMap($objectMap);
                        if ($parentId != -1) {
                            $parentPage = $em->getRepository("AppMainBundle:Page")->find($parentId);
                            $parentPage->addPage($page);
                            $em->persist($parentPage);
                        } else {
                            $objectMap->addPage($page);
                            $em->persist($objectMap);
                        }
                        $page->setSelected(true);
                        $em->flush();
                        $ajaxResponse['id'] = $page->getId();
                        $ajaxResponse['name'] = $page->getName();
                        $ajaxResponse['description'] = $page->getDescription();
                        $ajaxResponse['pagesCount'] = $objectMap->getPagesCount();
                        $ajaxResponse["treeObjectMap"] = $objectMap->getJsonTreeAsArray();
                    } catch (DBALException $e) {
                        if ($page->getName() == null || $page->getName() == "") {
                            $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                        } else {
                            $ajaxResponse['error'] = "ERROR: Name already used.";
                        }
                    }
                } else {
                    $ajaxResponse['error'] = (string) $form->getErrors(true);
                }
            } else {
                $ajaxResponse['error'] = "This object map does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/{id}/update/name",
     *          name="app_application_object_map_update_page_name_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updatePageNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $page = $em->getRepository('AppMainBundle:Page')->find($request->get("pk"));
            if ($page != null) {
                $page->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($page);
                if (count($errors) == 0) {
                    $em->persist($page);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "pageId" => $page->getId(),
                                "objectType" => "page"
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
                $response = new Response("Unknown page", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/{id}/update/description",
     *          name="app_application_object_map_update_page_description_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updatePageDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $page = $em->getRepository('AppMainBundle:Page')->find($request->get("pk"));
            if ($page != null) {
                $page->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($page);
                if (count($errors) == 0) {
                    $em->persist($page);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "pageId" => $page->getId(),
                                "objectType" => "page"
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
                $response = new Response("Unknown page", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/{id}/update/type",
     *          name="app_application_object_map_update_page_type_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updatePageTypeAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $page = $em->getRepository('AppMainBundle:Page')->find($request->get("pk"));
            if ($page != null) {
                $pageType = $em->getRepository('AppMainBundle:PageType')->find($request->get("value"));
                if ($pageType != null) {
                    $page->setPageType($pageType);
                    $validator = $this->get('validator');
                    $errors = $validator->validate($page);
                    if (count($errors) == 0) {
                        $em->persist($page);
                        $em->flush();
                        $response = new Response(json_encode(array(
                                    "pageId" => $page->getId(),
                                    "objectType" => "object",
                                    "pageTypeIcon" => $pageType->getIcon()
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
                    $response = new Response("Unknown page type", 400);
                }
            } else {
                $response = new Response("Unknown page", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/tree/selected/page/{id}",
     *      name="app_application_get_object_map_tree_with_selected_page_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("page", class="AppMainBundle:Page")
     */
    public function getObjectMapTreeWithSelectedPageAction(Page $page, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $page->setSelected(true);
            $objectMap = $page->getRootObjectMap();
            $ajaxResponse["tree-object-map-" . $objectMap->getId()] = $objectMap->getJsonTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/type/all", name="app_application_get_object_map_page_types_ajax", options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function getPageTypesAction(Request $request) {
        $ajaxResponse = array();
        if (($request->getMethod() == 'GET' || $request->getMethod() == 'POST') && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $pageTypes = $em->getRepository('AppMainBundle:PageType')->findAll();
            foreach ($pageTypes as $pageType) {
                $ajaxResponse[] = $pageType->getXEditableNode();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/{id}", name="app_application_get_object_map_page_object_ajax", requirements={"_method" = "post"}, options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("object", class="AppMainBundle:Object")
     */
    public function getObjectAction(Object $object, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $ajaxResponse["object"] = $object;
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/{id}/add/object",
     *          name="app_add_application_object_map_page_object_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("page", class="AppMainBundle:Page")
     */
    public function addObjectAction($page, Request $request) {
        $ajaxResponse = array();
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            if ($page !== null) {
                $form = $this->createForm(new ObjectType(), new Object());
                $form->handleRequest($request);
                if ($form->isValid()) {
                    try {
                        $object = $form->getData();
                        $page->addObject($object);
                        $em->persist($page);
                        $page->setSelected(true);
                        $em->flush();
                        $ajaxResponse['id'] = $object->getId();
                        $ajaxResponse['name'] = $object->getName();
                        $ajaxResponse['description'] = $object->getDescription();
                        $objectMap = $object->getPage()->getRootObjectMap();
                        $ajaxResponse['objectMapId'] = $objectMap->getId();
                        $ajaxResponse['objectsCount'] = $objectMap->getObjectsCount();
                        $ajaxResponse['treeObjectMap'] = $objectMap->getJsonTreeAsArray();
                    } catch (DBALException $e) {
                        if ($object->getName() == null || $object->getName() == "") {
                            $ajaxResponse['error'] = "ERROR: Name cannot be empty.";
                        } else {
                            $ajaxResponse['error'] = "ERROR: Name already used.";
                        }
                    }
                } else {
                    $ajaxResponse['error'] = (string) $form->getErrors(true);
                }
            } else {
                $ajaxResponse['error'] = "This page does not exist.";
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/{id}/update/name",
     *          name="app_application_object_map_page_update_object_name_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateObjectNameAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $object = $em->getRepository('AppMainBundle:Object')->find($request->get("pk"));
            if ($object != null) {
                $object->setName($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($object);
                if (count($errors) == 0) {
                    $em->persist($object);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "objectId" => $object->getId(),
                                "objectType" => "object"
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
                $response = new Response("Unknown object", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/{id}/update/description",
     *          name="app_application_object_map_page_update_object_description_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateObjectDescriptionAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $object = $em->getRepository('AppMainBundle:Object')->find($request->get("pk"));
            if ($object != null) {
                $object->setDescription($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($object);
                if (count($errors) == 0) {
                    $em->persist($object);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "objectId" => $object->getId(),
                                "objectType" => "object"
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
                $response = new Response("Unknown object", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/{id}/update/type",
     *          name="app_application_object_map_page_update_object_type_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateObjectTypeAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $object = $em->getRepository('AppMainBundle:Object')->find($request->get("pk"));
            if ($object != null) {
                $objectType = $em->getRepository('AppMainBundle:ObjectType')->find($request->get("value"));
                if ($objectType != null) {
                    $object->setObjectType($objectType);
                    $validator = $this->get('validator');
                    $errors = $validator->validate($object);
                    if (count($errors) == 0) {
                        $em->persist($object);
                        $em->flush();
                        $response = new Response(json_encode(array(
                                    "objectId" => $object->getId(),
                                    "objectType" => "object",
                                    "objectTypeIcon" => $objectType->getIcon()
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
                    $response = new Response("Unknown object type", 400);
                }
            } else {
                $response = new Response("Unknown object", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/tree/selected/object/{id}",
     *      name="app_application_get_object_map_tree_with_selected_object_ajax",
     *      requirements={"_method" = "post"},
     *      options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("object", class="AppMainBundle:Object")
     */
    public function getObjectMapTreeWithSelectedObjectAction(Object $object, Request $request) {
        $ajaxResponse = array();
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $object->setSelected(true);
            $objectMap = $object->getPage()->getRootObjectMap();
            $ajaxResponse["tree-object-map-" . $objectMap->getId()] = $objectMap->getJsonTreeAsArray();
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/type/all", name="app_application_get_object_map_page_object_types_ajax", options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function getObjectTypesAction(Request $request) {
        $ajaxResponse = array();
        if (($request->getMethod() == 'GET' || $request->getMethod() == 'POST') && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $objectTypes = $em->getRepository('AppMainBundle:ObjectType')->findAll();
            foreach ($objectTypes as $objectType) {
                $ajaxResponse[] = $objectType->getXEditableNode();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/identifier/type/all", name="app_application_get_object_map_page_object_identifier_types_ajax", options={"expose" = true })
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function getObjectIdentifierTypesAction(Request $request) {
        $ajaxResponse = array();
        if (($request->getMethod() == 'GET' || $request->getMethod() == 'POST') && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $objectIdentifierTypes = $em->getRepository('AppMainBundle:ObjectIdentifierType')->findAll();
            foreach ($objectIdentifierTypes as $objectIdentifierType) {
                $ajaxResponse[] = $objectIdentifierType->getXEditableNode();
            }
        }
        $response = new Response(json_encode($ajaxResponse));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/{id}/identifier/update/type",
     *          name="app_application_object_map_page_update_object_identifier_type_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateObjectIdentifierTypeAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $object = $em->getRepository('AppMainBundle:Object')->find($request->get("pk"));
            if ($object != null) {
                $objectIdentifierType = $em->getRepository('AppMainBundle:ObjectIdentifierType')->find($request->get("value"));
                if ($objectIdentifierType != null) {
                    $objectIdentifier = $object->getObjectIdentifier();
                    if ($objectIdentifier == null) {
                        $objectIdentifier = new ObjectIdentifier();
                        $object->setObjectIdentifier($objectIdentifier);
                    }
                    $objectIdentifier->setObjectIdentifierType($objectIdentifierType);
                    $validator = $this->get('validator');
                    $errors = $validator->validate($objectIdentifier);
                    if (count($errors) == 0) {
                        $em->persist($object);
                        $em->flush();
                        $response = new Response(json_encode(array(
                                    "objectId" => $object->getId(),
                                    "objectType" => "object"
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
                    $response = new Response("Unknown object identifier type", 400);
                }
            } else {
                $response = new Response("Unknown object", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/page/object/{id}/identifier/update/value",
     *          name="app_application_object_map_page_update_object_identifier_value_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function updateObjectIdentifierValueAction(Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $object = $em->getRepository('AppMainBundle:Object')->find($request->get("pk"));
            if ($object != null) {
                $objectIdentifier = $object->getObjectIdentifier();
                if ($objectIdentifier == null) {
                    $objectIdentifier = new ObjectIdentifier();
                    $object->setObjectIdentifier($objectIdentifier);
                }
                $objectIdentifier->setValue($request->get("value"));
                $validator = $this->get('validator');
                $errors = $validator->validate($objectIdentifier);
                if (count($errors) == 0) {
                    $em->persist($object);
                    $em->flush();
                    $response = new Response(json_encode(array(
                                "objectId" => $object->getId(),
                                "objectType" => "object"
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
                $response = new Response("Unknown object", 400);
            }
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/application/object/map/{id}/objects/delete",
     *          name="app_application_object_map_objects_delete_ajax",
     *          requirements={"_method" = "post"},
     *          options={"expose" = true }
     * )
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("objectMap", class="AppMainBundle:ObjectMap")
     */
    public function deleteObjectsAction($objectMap, Request $request) {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $objects = $request->get("objects");
            $selectedNode = $request->get("selectedNode");
            $em = $this->getDoctrine()->getManager();
            foreach ($objects as $object) {
                $href = $object["href"];
                $id = substr($href, strrpos($href, "-") + 1);
                $type = substr($href, strpos($href, "-") + 1, strrpos($href, "-") - strpos($href, "-") - 1);
                if ($type == "page") {
                    $persistedObject = $em->getRepository('AppMainBundle:Page')->find($id);
                } elseif ($type == "object") {
                    $persistedObject = $em->getRepository('AppMainBundle:Object')->find($id);
                }
                $em->remove($persistedObject);
            }
            $em->flush();
            $objectMap = $em->getRepository('AppMainBundle:ObjectMap')->find($objectMap->getId());
            $ajaxResponse = array();
            $ajaxResponse['count'] = count($objects);
            $ajaxResponse['objectMapId'] = $objectMap->getId();
            $ajaxResponse['objectsCount'] = $objectMap->getObjectsCount();
            $ajaxResponse['pagesCount'] = $objectMap->getPagesCount();
            $ajaxResponse['treeObjectMap'] = $objectMap->getJsonTreeAsArray();
            $response = new Response(json_encode($ajaxResponse));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
