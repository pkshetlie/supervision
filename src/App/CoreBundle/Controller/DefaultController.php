<?php

namespace App\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function loginAction()
    {
        return $this->render('CoreBundle:Default:login.html.twig');

    }

    public function indexAction()
    {
        if(!$this->isGranted("ROLE_USER")){
            return $this->redirectToRoute("fos_user_security_login");
        }
        return $this->render("@Theme/Default/index.html.twig");
    }

}
