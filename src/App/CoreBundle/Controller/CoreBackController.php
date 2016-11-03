<?php

namespace App\CoreBundle\Controller;

use App\CoreBundle\Entity\EntityCore;
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

    public function indexAction(Request $request)
    {
        return $this->render($this->getCrudDir() . ':index.html.twig', array(
            "fields"=> $this->_tableHead,
            "titleD"=> 'crud.' . $this->getEntityRouteName() . '.titleR',
            'entityRouteName' => $this->getEntityRouteName(),
        ));
    }


    public function doCreateEdit(Request $request, IEntity $entity = null)
    {
        if (null === $this->_entityClassName) {
            throw $this->createNotFoundException('Unable to find entity class Name.');
        }
        if ($entity == null) {
            $entity = new $this->_entityClassName();
        }
        $edit = false;
        if($entity->getId()){
            $edit = true;
        }
        if (!$entity) {
            throw $this->createNotFoundException('Unable to get object for entity "' . $this->_entityClassName . '"');
        }
        $em = $this->getDoctrine()->getManager();

        $form = $this->createNewEditForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if($this->_ownerOnly){
                $entity->setUser($this->getUser());
            }
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.edit'));
            return $this->redirect($this->generateUrl('crud_' . $this->getEntityRouteName() . '_create_edit', ['id' => $entity->getId()]));
        }
        return $this->render(
            $this->getCrudDir() . ':create_edit.html.twig', [
                'entity' => $entity,
                'form' => $form->createView(),
                'titleCU' =>'crud.' . $this->getEntityRouteName() . '.title'.($edit?"U":"C")
            ]
        );
    }


    /**
     * Creates a form to create a entity.
     *
     * @param $entity
     *
     * @return \Symfony\Component\Form\Form The form
     *
     */
    private function createNewEditForm(IEntity $entity)
    {
        $form = $this->createForm($this->getEntityFormType(), $entity, array(
                'method' => 'POST',
            )
        );
        $form->add('submit', SubmitType::class, array(
                'label' => 'crud.common.form.' . ($entity->getId() !== null ? 'update' : 'create'),
                'attr' => array(
                    'class' => 'btn btn-primary',
                ),
            )
        );
        return $form;
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
        $em = $this->getDoctrine()->getManager();
        if ($entity === null) {
            throw $this->createNotFoundException('Unable to find entity.');
        }
        try {
            $em->remove($entity);
            $em->flush();
            $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.delete.success'));
        } catch (Exception $e) {
            $this->addFlash('success', $this->get('translator')->trans('crud.' . $this->getEntityRouteName() . '.flash.delete.error'));
        }
        return $this->redirectToRoute('crud_' . $this->getEntityRouteName() . '_index');
    }


    /**
     * @return string something like "shop"
     */
    protected abstract function getEntityRouteName();

    /**
     * @return string something like "BackShopBundle:Crud" or null
     */
    protected abstract function getCrudDirectory();

    /**
     * @return string something like ShopType::class
     */
    protected abstract function getEntityFormType();

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

    protected function getEntitiesAndCount(Request $request)
    {
        return array($this->getDoctrine()->getRepository($this->_entityClassName)->findAllForDataTable($request,$this->_tableHead, $this->_ownerOnly ? $this->getUser() : null),
            $total = $this->getDoctrine()->getRepository($this->_entityClassName)->count( $this->_ownerOnly ? $this->getUser() : null));

    }

    public function dataTableAction(Request $request)
    {
        list($entities,$total) = $this->getEntitiesAndCount($request);
        $data = array();
        $i = 0;
        /** @var IEntity $entity */
        foreach($entities AS $entity){
            foreach($this->_tableHead AS $field){
                $data[$i][$field] = $entity->get($field);
            }
            $data[$i]["DT_RowId"]= "row_".$i;
            $data[$i]["DT_RowData"]= array(
                "pkey"=> $i
        );
            $data[$i]['action_dataTable'] = '<a href="'.$this->generateUrl("crud_".$this->getEntityRouteName()."_create_edit", array("entity"=>$entity->get("id"))).'" class="btn btn-xs  btn-primary" ><i class="fa fa-edit"></i></a>
            <a href="'.$this->generateUrl("crud_".$this->getEntityRouteName()."_delete", array("entity"=>$entity->get("id"))).'" class="btn btn-xs btn-delete btn-danger" ><i class="fa fa-trash"></i></a>';
            $data[$i] = (object)$data[$i];
            $i++;
        }

        return new JsonResponse(
            array(
                "draw" => $request->get('draw',0),
                "recordsTotal" => (int)$total,
                "recordsFiltered" => count($entities),
                "data" => $data
            )
        );
    }
}