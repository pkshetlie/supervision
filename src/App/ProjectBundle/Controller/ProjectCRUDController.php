<?php

namespace App\ProjectBundle\Controller;

use App\CoreBundle\Controller\CoreBackController;
use App\ProjectBundle\Entity\Project;
use App\ProjectBundle\Form\ProjectType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectCRUDController extends CoreBackController
{

    public function countNewErrorsAction()
    {
        $errors = $this->getDoctrine()->getRepository("ProjectBundle:Error")->getNew($this->getUser());
        return new Response(count($errors));
    }

    protected $_ownerOnly = true;
    protected $_entityClassName = Project::class;
    protected $_tableHead = array(
        'id',
        'label',
    );

    /**
     * @return string something like "BackShopBundle:Crud"
     */
    protected function getCrudDirectory()
    {
//        return 'BackShopBundle:Crud';
    }

    public function deleteAction(Project $entity = null)
    {
        return parent::doDelete($entity);
    }
    public function createEditAction(Request $request, Project $entity = null)
    {
        return parent::doCreateEdit($request,$entity);
    }
    /**
     * @return string something like "shop"
     */
    protected function getEntityRouteName()
    {
        return 'project';
    }

    /**
     * @return string something like ShopType::class
     */
    protected function getEntityFormType()
    {
        return ProjectType::class;
    }


}
