<?php
namespace Cerad\Bundle\TournBundle\Listeners;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

//  Symfony\Component\Security\Core\SecurityContextInterface;
//  Symfony\Component\Routing\RouterInterface;
//  Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Takes care of creating a model
class RequestModelListener extends ContainerAware implements EventSubscriberInterface
{
    protected $redirectRoute = 'cerad_tourn_welcome';
    
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::REQUEST => array(
                array('onKernelRequest', -256), // Want this run after all the app listeners
        ));
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        return;
        
        // Only process routes with a model
        $request           = $event->getRequest();
        $requestAttributes = $request->attributes;
        
        $modelFactoryServiceId = $requestAttributes->get('_model');
        
        if (!$modelFactoryServiceId) return;
   
        // Probably getting too cute here
        // $requestAttributes->set('refererUrl',$request->headers->get('referer'));
        
        // Create the model
        $modelFactory = $this->container->get($modelFactoryServiceId);
        
        // Throws exceptions on errors
        try 
        {
            $model = $modelFactory->create($requestAttributes);
        }
        catch (\Exception $e)
        {
            // TODO: Handle this in exception controller
            // Or maybe just change the controller here?
            throw $e;
            
            $router = $this->container->get('router');
            
            $redirect = $requestAttributes->get('_redirect');
            
            if (!$redirect) $redirect = $this->redirect;
            
            $url = $router->generate($this->redirect);
        
            $response = new RedirectResponse($url);
        
            $event->setResponse($response);
            
            return;
        }
        $requestAttributes->set('model',$model);
        
        // Have a form?
        $formFactoryServiceId = $requestAttributes->get('_form');
        if (!$formFactoryServiceId) return;
        
        $formFactory = $this->container->get($formFactoryServiceId);
       
        try
        {
            $form = $formFactory->create($requestAttributes,$model);
        }
        catch (\Exception $e)
        {
            throw $e;
        }
        $requestAttributes->set('form',$form);
    }
}
?>
