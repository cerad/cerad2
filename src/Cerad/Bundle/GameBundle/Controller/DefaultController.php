<?php

namespace Cerad\Bundle\GameBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CeradGameBundle:Default:index.html.twig', array('name' => $name));
    }
}
