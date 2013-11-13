<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournIFrameController extends MyBaseController
{
    public function iframeAction(Request $request)
    {
        return new RedirectResponse('http://ayso1ref.com/s1_13/zayso', 302);
    }
}
