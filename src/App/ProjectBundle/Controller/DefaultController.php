<?php

namespace App\ProjectBundle\Controller;

use App\ProjectBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ProjectBundle:Default:index.html.twig');
    }

    public function countNewErrorsAction()
    {
        $errors = $this->getDoctrine()->getRepository("ProjectBundle:Error")->getNew($this->getUser());

        return new Response(count($errors));
    }

    public function createEditAction(Request $request, Project $project = null)
    {


    }

}
