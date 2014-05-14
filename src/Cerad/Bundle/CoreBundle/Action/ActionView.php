<?php

namespace Cerad\Bundle\CoreBundle\Action;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ActionView
{
    protected $router;
    protected $templating;
    
    public function setRouter    (RouterInterface $router)     { $this->router     = $router;     }
    public function setTemplating(EngineInterface $templating) { $this->templating = $templating; }
    
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }   
    protected function redirectResponse($path, $params = array())
    {
        return new RedirectResponse($this->generateUrl($path,$params));
    }
    protected function regularResponse($tplName, $tplData = array())
    {
        return $this->templating->renderResponse($tplName, $tplData);
    }
}
