<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marie
 * Date: 26/10/13
 * Time: 13:16
 * To change this template use File | Settings | File Templates.
 */

namespace Ace\BlogBundle\Menu;


use Ace\BlogBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Articles', array('route' => 'post'));
        $menu->addChild('Users', array('route' => 'users'));

        if ($this->container->get('security.context')->getToken() instanceof TokenInterface &&
            $this->container->get('security.context')->getToken()->getUser() instanceof User) {
            $menu->addChild('My profile', array('route' => 'profile'));
            $menu->addChild('Logout', array('route' => 'logout'));
        } else {
            $menu->addChild('Login', array('route' => 'login'));
            $menu->addChild('Create account', array('route' => 'user_create_account'));
        }

        return $menu;
    }
}