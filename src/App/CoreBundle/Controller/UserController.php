<?php

namespace App\CoreBundle\Controller;

use App\CoreBundle\Entity\User;
use App\CoreBundle\Form\UserType;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends CoreBackController
{

    protected $_ownerOnly = false;
    /** @var  UserInterface */
    protected $_entity;
    protected $_entityClassName = "App\CoreBundle\Entity\User";
    protected $_tableHead = array(
        'societe',
        'label',
        'email',
        'username',
        'lastLogin',
        'enabled',
    );

    public function deleteAction(User $entity = null)
    {
        $entity->setEnabled(false);

        return parent::doDelete($entity);
    }

    public function createUpdateAction(Request $request, User $entity = null)
    {
        return parent::doCreateUpdate($request, $entity);
    }

    /**
     * @return string something like "BackShopBundle:Crud"
     */
    protected function getCrudDirectory()
    {
//        return 'BackShopBundle:Crud';
    }

    /**
     * @return string something like "shop"
     */
    protected function getEntityRouteName()
    {
        return 'user';
    }

    protected function beforeFormCreateUpdate()
    {
        if ($this->_entity->getId() == null) {
            $this->_entity->setEnabled(true);
        }
        return parent::beforeFormCreateUpdate();
    }

    protected function beforePersistCreateUpdate()
    {
        $userRepo = $this->getDoctrine()->getRepository("CoreBundle:User");
        $existingUser = $userRepo->findOneBy(array("username"=>$this->_entity->getUsername()));
        if($existingUser != null && ($existingUser->getId() != $this->_entity->getId() || $this->_entity->getId() == null)){
            $this->addFlash(
                'danger',
                'Cet utilisateur existe déjà'
            );
            return false;
        }
        $existingUser = $userRepo->findOneBy(array("email"=>$this->_entity->getEmail()));
        if($existingUser != null && ($existingUser->getId() != $this->_entity->getId() || $this->_entity->getId() == null)){
            $this->addFlash(
                'danger',
                'Cet adresse mail est déjà utilisée'
            );
            return false;
        }
        if ($this->_entity->getPlainPassword()) {
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($this->_entity);
            $this->_entity->setPassword($encoder->encodePassword($this->_entity->getPlainPassword(), $this->_entity->getSalt()));
        }
        return parent::beforeFormCreateUpdate();
    }

    /**
     * @return string something like ShopType::class
     */
    protected function getEntityFormType()
    {
        return "App\CoreBundle\Form\UserType";
    }


}
