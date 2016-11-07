<?php

namespace App\ProjectBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ErrorRestController extends Controller
{

    public function getErrorAction()
    {
        $data = $this->getDoctrine()->getRepository("ProjectBundle:Error")->findAll(); // get data, in this case list of users.

        return $data;
    }

    /**
     * @ParamConverter("post", converter="fos_rest.request_body")
     */
    public function putPostAction(Post $post)
    {
        // ...
    }
}
