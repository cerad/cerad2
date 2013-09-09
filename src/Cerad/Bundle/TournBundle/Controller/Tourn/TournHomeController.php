<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournHomeController extends MyBaseController
{
    public function homeAction(Request $request)
    {
        $tplData = array();
        return $this->render('@CeradTourn/Tourn/Home/TournHomeIndex.html.twig', $tplData);
    }
}
