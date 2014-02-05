<?php
namespace Cerad\Bundle\TournBundle\Listeners;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Security\Core\SecurityContextInterface;

use Symfony\Component\Routing\RouterInterface;
//  Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/* ==========================================================
 * This needs to run after the firewall does it's thing
 * 
 */
class RequestRoleListener implements EventSubscriberInterface
{
    protected $redirectTo;
    
    protected $router;
    protected $securityContext;
    
    public function __construct(SecurityContextInterface $securityContext, RouterInterface $router)
    {
        $this->router          = $router;
        $this->securityContext = $securityContext;
        
        // Maybe passed in
        $this->redirectTo = 'cerad_tourn_welcome';
    }
    public static function getSubscribedEvents()
    {
        return array
        (
            KernelEvents::REQUEST => array(
                array('onKernelRequest', -4), // RouterListener = 32, Firewall = 8
        ));
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $role = $event->getRequest()->attributes->get('_role');
        if (!$role) return;
        
        if ($this->securityContext->isGranted($role)) return;
        
        $url = $this->router->generate($this->redirectTo);
        
        $response = new RedirectResponse($url);
        
        $event->setResponse($response);
        
      //die('Denied ' . $role);
    }
}
?>
