<?php

namespace App\CoreBundle\Controller;

use App\CoreBundle\Interfaces\IEntity;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


abstract class CoreBackController extends Controller
{

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
                if (gettype($obj) == gettype(new \DateTime())) {
                    $str = $obj->format("d/m/Y H:i");
                } elseif (is_object($obj)) {
                    $str = $obj->__toString();
                }
                $data[$i][$field] = $str;
            }
            $data[$i]["DT_RowId"] = "row_" . $i;
            $data[$i]["DT_RowData"] = array(
                "pkey" => $i
            );
            $data[$i]['action_dataTable'] = '<a href="' . $this->generateUrl("crud_" . $this->getEntityRouteName() . "_create_edit", array("entity" => $entity->get("id"))) . '" class="btn btn-xs  btn-primary" ><i class="fa fa-edit"></i></a>
            <a href="' . $this->generateUrl("crud_" . $this->getEntityRouteName() . "_delete", array("entity" => $entity->get("id"))) . '" class="btn btn-xs btn-delete btn-danger" ><i class="fa fa-trash"></i></a>';
            $data[$i] = (object)$data[$i];
            $i++;
        }

        return new JsonResponse(
            array(
                "draw" => $request->get('draw', 0),
                "recordsTotal" => (int)$total,
                "recordsFiltered" => count($entities),
                "data" => $data
            )
        );
    }

    public function doCreateUpdate(Request $request, IEntity $entity = null)
    {
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
            $this->beforePersistCreateUpdate();
            $em->persist($this->_entity);
            $this->afterPersistCreateUpdate();
            $em->flush();
            $this->afterFlushCreateUpdate();
            $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.edit'));
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
            $this->beforeRemove();
            if ($this->_useTrueDelete) {
                $em->remove($this->_entity);
            } else {
                $this->_entity->setIsDeleted(true);
            }
            $this->afterRemove();

            $em->flush();
            $this->afterFlushDelete();
            $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.delete.success'));
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
    {
    }

    protected function beforeFormCreateUpdate(){}
    protected function beforePersistDelete()
    {
    }

    protected function getEntitiesAndCount(Request $request)
    {
        $this->_request = $request;
        return array($this->getDoctrine()->getRepository($this->_entityClassName)->findAllForDataTable($request, $this->_tableHead, $this->_ownerOnly ? $this->getUser() : null),
            $total = $this->getDoctrine()->getRepository($this->_entityClassName)->count($this->_ownerOnly ? $this->getUser() : null));

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
    }

    protected function afterPersistCreateUpdate()
    {
    }

    protected function afterFlushCreateUpdate()
    {
    }

    /**
     * @return string something like "BackShopBundle:Crud" or null
     */
    protected abstract function getCrudDirectory();

    protected function beforeRemove()
    {
    }

    protected function afterRemove()
    {
    }

    protected function afterFlushDelete()
    {
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
        $form->add('submit', SubmitType::class, array(
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