<?php

namespace Cerad\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CeradUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
