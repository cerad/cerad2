<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournIFrameController extends MyBaseController
{
    public function iframeAction(Request $request)
    {
        $session = $request->getSession();
        $session->set('iframe','called');
        
        $redirect = $request->get('redirect');
        if (!$redirect) $redirect = 'http://ayso1ref.com/s1_13/_zayso';
        
        return new RedirectResponse($redirect, 302);
    }
}
