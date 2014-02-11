<?php
namespace Cerad\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/* ========================================================
 * Rather poorly named but takes care of creating the model,form and possible view
 * 
 * It will probably implement the role listener as well
 * 
 * App Request Priority
 * 
 * -256 Role/Model/Form/View
 */
class CoreRequestListener extends ContainerAware implements EventSubscriberInterface
{
    const UserEventListenerPriority    =  -16;
    const ProjectEventListenerPriority =  -32;
    const GameEventListenerPriority    =  -64;
    const PersonEventListenerPriority  =  -64;
    const ModelRequestListenerPriority = -256;
    
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::REQUEST => array(array('onKernelRequest', self::ModelRequestListenerPriority),
        ));
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        // Will a sub request ever change this?
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) return;
        
        // Only process routes asking for a model
        if (!$event->getRequest()->attributes->has('_model')) return;
        
        // Only process routes with a model
        $request      = $event->getRequest();
        $requestAttrs = $request->attributes;
        
        $modelFactoryServiceId = $requestAttrs->get('_model');
        
        $modelFactory = $this->container->get($modelFactoryServiceId);
        
        $model = $modelFactory->create($request);
        
        $requestAttrs->set('model',$model);
        
        // Have a form?
        $formFactoryServiceId = $requestAttrs->get('_form');
        if (!$formFactoryServiceId) return;
        
        $formFactory = $this->container->get($formFactoryServiceId);
       
        $form = $formFactory->create($request,$model);
        
        $requestAttrs->set('form',$form);
    }
}
