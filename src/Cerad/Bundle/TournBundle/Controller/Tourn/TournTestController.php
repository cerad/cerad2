<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournTestController extends MyBaseController
{
    public function testAction(Request $request)
    {
        $tplData = array();
        return $this->render('@CeradTourn/Tourn/Test/TournTestIndex.html.twig', $tplData);
    }
}
