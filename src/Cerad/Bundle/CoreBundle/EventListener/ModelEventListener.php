<?php

namespace Cerad\Bundle\CoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;

//  Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
//  Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class ModelEventListener extends ContainerAware implements EventSubscriberInterface
{
    const ControllerRoleEventListenerPriority  = -1100;
    const ControllerModelEventListenerPriority = -1900;
    const ControllerFormEventListenerPriority  = -1910;
    
    const ViewEventListenerPriority = -1900;

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(
              //array('doRole',          -1100),
              //array('doUser',          -1200),  // Logged in user
              //array('doUserPerson',    -1210),  // Logged in user person
              //array('doUserFind',      -1220),  // Passed as argument
              //array('doProject',       -1300),
              //array('doPerson',        -1400),
              //array('doProjectPerson', -1210),
                
              //array('doGame',          -1600),
                
                array('onControllerRole',  self::ControllerRoleEventListenerPriority),
                array('onControllerModel', self::ControllerModelEventListenerPriority),
                array('onControllerForm',  self::ControllerFormEventListenerPriority),
            ),
            KernelEvents::VIEW => array(
                array('onView', self::ViewEventListenerPriority),
            ),
        );
    }
    /* =================================================================
     * Creates and renders a view
     */
    public function onView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
       
        if ($request->attributes->has('_format')) 
        {
            $viewAttrName = '_view_' . $request->attributes->get('_format');
        }
        else $viewAttrName = '_view';
        
        if (!$request->attributes->has($viewAttrName)) return;
        
        $viewServiceId = $request->attributes->get($viewAttrName);
        
        $view = $this->container->get($viewServiceId);
     
        $response = $view->renderResponse($request); // Maybe should just be model?
        
        $event->setResponse($response);
    }
    /* =============================================================
     * Allows protecting each route while defining the route
     * Question: Should this also take care of grabbing and injecting the user
     */
    public function onControllerRole(FilterControllerEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_role')) return;
        
        $role = $event->getRequest()->attributes->get('_role');
        
        $securityContext = $this->container->get('security.context');
        if (!$securityContext->isGranted($role))
        {
            throw new AccessDeniedException(); 
        }
    }
    /* ==========================================================
     * The Model
     * Does get called in sub requests
     */
    public function onControllerModel(FilterControllerEvent $event)
    {
        if (!$event->getRequest()->attributes->has('_model')) return;
        
        $request = $event->getRequest();
        
        $modelFactoryServiceId = $request->attributes->get('_model');
        
        $modelFactory = $this->container->get($modelFactoryServiceId);
     
        $model = $modelFactory->create($request);
        
        $request->attributes->set('model',$model);
    }
    /* ==========================================================
     * The Model Form
     */
    public function onControllerForm(FilterControllerEvent $event)
    {   
        if (!$event->getRequest()->attributes->has('_form')) return;
        
        $request = $event->getRequest();
        
        $formFactoryServiceId = $request->attributes->get('_form');
        
        $formFactory = $this->container->get($formFactoryServiceId);
        
        $form = $formFactory->create($request,$request->attributes->get('model'));
        
        $request->attributes->set('form',$form);
    }
}
