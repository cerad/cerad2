<?php
namespace Cerad\Bundle\TournBundle\Listeners;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResponseListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            'kernel.response' => array(
                array('onKernelResponse', 10),
        ));
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        // P3P Policy
        $event->getResponse()->headers->set('P3P', 
            'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
    }
}
?>
