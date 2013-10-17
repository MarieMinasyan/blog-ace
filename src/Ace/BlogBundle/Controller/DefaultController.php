<?php

namespace Ace\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('AceBlogBundle:Default:index.html.twig', array('name' => $name));
    }
}
