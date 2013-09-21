<?php

namespace Cerad\Bundle\LevelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CeradLevelBundle:Default:index.html.twig', array('name' => $name));
    }
}
