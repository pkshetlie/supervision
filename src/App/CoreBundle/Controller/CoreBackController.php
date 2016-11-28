<?php

namespace App\CoreBundle\Controller;

use App\CoreBundle\Interfaces\IEntity;
use Doctrine\ORM\PersistentCollection;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @property
 */
abstract class CoreBackController extends Controller
{
    protected $_activeEditDelete = true;
    protected $_entityClassName;
    protected $_tableHead;
    protected $_ownerOnly = false;
    protected $_useTrueDelete = false;
    /**
     * @var IEntity
     */
    protected $_entity;
    /**
     * @var Request
     */
    protected $_request;


    public function dataTableAction(Request $request)
    {
        $this->_request = $request;
        list($entities, $total) = $this->getEntitiesAndCount($request);
        $data = array();
        $i = 0;
        /** @var IEntity $entity */
        foreach ($entities AS $entity) {
            foreach ($this->_tableHead AS $field) {
                $str = $obj = $entity->get($field);
                if ($obj instanceof \DateTime ) {
                    $str = $obj->format("d/m/Y H:i");
                }elseif($obj instanceof PersistentCollection){
                    $tmp = array();
                    foreach($obj AS $ob){
                        $tmp[]= $ob->getLabel();
                    }
                    $str = implode(', ',$tmp);

                } elseif (is_object($obj)) {
                    $str = $obj->__toString();
                }
                $data[$i][$field] = $str;
            }
            $data[$i]["DT_RowId"] = "row_" . $i;
            $data[$i]["DT_RowData"] = array(
                "pkey" => $i
            );
            if ($this->_activeEditDelete) {
                $data[$i]['action_dataTable'] =
                    '<a href="' . $this->generateUrl("crud_" . $this->getEntityRouteName() . "_create_edit", array("entity" => $entity->get("id"))) . '" class="btn btn-xs  btn-primary" ><i class="fa fa-edit"></i></a>
                <a href="' . $this->generateUrl("crud_" . $this->getEntityRouteName() . "_delete", array("entity" => $entity->get("id"))) . '" class="btn btn-xs btn-delete btn-danger" onclick="javascript:return confirm(\'Vous allez supprimer un élément, confirmez vous cette action ?\')" ><i class="fa fa-trash"></i></a>';
            }
            $data[$i] = (object)$data[$i];
            $i++;
        }

        return new JsonResponse(
            array(
                "draw" => $request->get('draw', 0),
                "recordsTotal" => (int)$total,
                "recordsFiltered" => (int)$total,
                "data" => $data
            )
        );
    }

    public function doCreateUpdate(Request $request, IEntity $entity = null)
    {
        try {
            $this->_request = $request;
            $this->_entity = $entity;
            if (null === $this->_entityClassName) {
                throw $this->createNotFoundException('Unable to find entity class Name.');
            }
            if ($this->_entity == null) {
                $this->_entity = new $this->_entityClassName();
            }
            $edit = false;
            if ($this->_entity->getId()) {
                $edit = true;
            }
            if (!$this->_entity) {
                throw $this->createNotFoundException('Unable to get object for entity "' . $this->_entityClassName . '"');
            }
            $em = $this->getDoctrine()->getManager();
            $this->beforeFormCreateUpdate();
            $form = $this->createCreateUpdateForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($this->_ownerOnly) {
                    $this->_entity->setUser($this->getUser());
                }

                if ($this->beforePersistCreateUpdate()) {
                    $em->persist($this->_entity);
                    if ($this->afterPersistCreateUpdate()) {
                        $em->flush();
                        $this->afterFlushCreateUpdate();
                        $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.edit'));
                        return $this->redirect($this->generateUrl('crud_' . $this->getEntityRouteName() . '_index'));

                    }else{
                        return $this->render(
                            $this->getCrudDir() . ':create_edit.html.twig', [
                                'entity' => $this->_entity,
                                'form' => $form->createView(),
                                'titleCU' => 'crud.' . $this->getEntityRouteName() . '.title' . ($edit ? "U" : "C")
                            ]
                        );
                    }
                }else {
                    return $this->render(
                        $this->getCrudDir() . ':create_edit.html.twig', [
                            'entity' => $this->_entity,
                            'form' => $form->createView(),
                            'titleCU' => 'crud.' . $this->getEntityRouteName() . '.title' . ($edit ? "U" : "C")
                        ]
                    );
                }

            }
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirect($this->generateUrl('crud_' . $this->getEntityRouteName() . '_index'));

        }
        return $this->render(
            $this->getCrudDir() . ':create_edit.html.twig', [
                'entity' => $this->_entity,
                'form' => $form->createView(),
                'titleCU' => 'crud.' . $this->getEntityRouteName() . '.title' . ($edit ? "U" : "C")
            ]
        );
    }

    /**
     * @param Request $request
     * @param IEntity $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     */
    public function doDelete(IEntity $entity = null)
    {

        $this->_entity = $entity;
        $em = $this->getDoctrine()->getManager();
        if ($this->_entity === null) {
            throw $this->createNotFoundException('Unable to find entity.');
        }
        try {
            if ($this->beforeRemove()) {
                if ($this->_useTrueDelete) {
                    $em->remove($this->_entity);
                } else {
                    $this->_entity->setIsDeleted(true);
                }
                if ($this->afterRemove()) {
                    $em->flush();
                    $this->afterFlushDelete();
                    $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.delete.success'));
                }

            }
        } catch (Exception $e) {
            $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.delete.error'));
        }
        return $this->redirectToRoute('crud_' . $this->getEntityRouteName() . '_index');
    }

    public function indexAction(Request $request)
    {
        return $this->render($this->getCrudDir() . ':index.html.twig', array(
            "fields" => $this->_tableHead,
            "titleD" => 'crud.' . $this->getEntityRouteName() . '.titleR',
            'entityRouteName' => $this->getEntityRouteName(),
        ));
    }

    protected function afterPersistDelete()
    {        return true;

    }

    protected function beforeFormCreateUpdate()
    {
        return true;
    }

    protected function beforePersistDelete()
    {        return true;

    }

    protected function getEntitiesAndCount(Request $request)
    {
        $this->_request = $request;

        return array($this->getDoctrine()->getRepository($this->_entityClassName)->findAllForDataTable($request, $this->_tableHead, $this->_ownerOnly ? $this->getUser() : null),
            $total = $this->getDoctrine()->getRepository($this->_entityClassName)->count($this->_ownerOnly ? $this->getUser() : null,$request,$this->_tableHead));

    }

    /**
     * @return string something like "shop"
     */
    protected abstract function getEntityRouteName();

    /**
     * @return string something like ShopType::class
     */
    protected abstract function getEntityFormType();

    protected function beforePersistCreateUpdate()
    {
        return true;
    }

    protected function afterPersistCreateUpdate()
    {
        return true;
    }

    protected function afterFlushCreateUpdate()
    {
        return true;
    }

    /**
     * @return string something like "BackShopBundle:Crud" or null
     */
    protected abstract function getCrudDirectory();

    protected function beforeRemove()
    {
        return true;
    }

    protected function afterRemove()
    {
        return true;
    }

    protected function afterFlushDelete()
    {
        return true;
    }

    /**
     * Creates a form to create a entity.
     *
     * @param $entity
     *
     * @return \Symfony\Component\Form\Form The form
     *
     */
    private function createCreateUpdateForm()
    {
        $form = $this->createForm($this->getEntityFormType(), $this->_entity, array(
                'method' => 'POST',
            )
        );
        $form->add('submit', "submit", array(
                'label' => 'crud.common.form.' . ($this->_entity->getId() !== null ? 'update' : 'create'),
                'attr' => array(
                    'class' => 'btn btn-primary',
                ),
            )
        );
        return $form;
    }

    /**
     * @return string
     */
    private function getCrudDir()
    {
        if ($this->getCrudDirectory() === null) {
            return "CoreBundle:Crud";
        }
        return $this->getCrudDirectory();
    }


}