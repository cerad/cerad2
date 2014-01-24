<?php
namespace Cerad\Bundle\TournBundle\Controller;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Symfony\Component\HttpKernel\KernelInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\Form\FormFactoryInterface;

//  Symfony\Component\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Psr\Log\LoggerInterface;

class ActionHelper
{
    protected $logger;
    protected $kernel;
    protected $router;
    protected $securityContext;
    protected $formFactory;
    protected $templating;
    
    public function __construct(
        LoggerInterface          $logger,
        KernelInterface          $kernel,
        RouterInterface          $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface     $formFactory,
        EngineInterface          $templating)
    {
        $this->logger          = $logger;
        $this->kernel          = $kernel;
        $this->router          = $router;
        $this->securityContext = $securityContext;
        $this->formFactory     = $formFactory;
        $this->templating      = $templating;
    }
    public function getLogger() { return $this->logger; }
    
    public function getUser()
    {
        $token = $this->securityContext->getToken();
        if (!$token) return null;

        $user = $token->getUser();
        if (!is_object($user)) return null;
        
        return $user;
    }
    public function hasRole($role)
    {
        return $this->securityContext->isGranted($role);
    }
    public function generateRedirectResponse($route, $params = array())
    {
        $url = $this->generateUrl($route,$params);
        
        return new RedirectResponse($url);
    }
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }
    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }
    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->formFactory->createBuilder('form', $data, $options);
    }
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }
    public function renderView($view, array $parameters = array())
    {
        return $this->templating->render($view, $parameters);
    }

}
