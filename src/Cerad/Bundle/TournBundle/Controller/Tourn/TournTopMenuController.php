<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournTopMenuController extends MyBaseController
{
    public function renderAction(Request $request)
    {
        $tplData = array();
        $tplData['user']    = $this->getUser();
        $tplData['project'] = $this->getProject();
        return $this->render('@CeradTourn/Tourn/TopMenu/TournTopMenuIndex.html.twig', $tplData);
    }
}
