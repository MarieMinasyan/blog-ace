<?php

namespace Ace\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    private $isAdmin;

    public function __construct($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array('required' => true))
            ->add('password', 'password', array('required' => true))
        ;

        if ($this->isAdmin) {
            $builder->add('userRoles', 'entity', array(
                'class' => 'AceBlogBundle:Role',
                'property' => 'name',
                'expanded' => false,
                'multiple' => true,
                'required' => true
            ));
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ace\BlogBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ace_blogbundle_user';
    }
}
