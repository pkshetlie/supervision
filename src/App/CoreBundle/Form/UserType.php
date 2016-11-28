<?php

namespace App\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('societe', null, array('label'=>'user.societe'))
            ->add('label', null, array('label'=>'user.label'))
            ->add('username', null, array('label'=>'user.username'))
            ->add('email', "email", array('label'=>'user.email'))
            ->add('plainPassword', "password", array('label'=>'user.password',"required"=>false))
            ->add('enabled', "checkbox", array('label'=>'user.enabled',"required"=>false))

        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\CoreBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_corebundle_user';
    }


}
