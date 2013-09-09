<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournHomeController extends MyBaseController
{
    public function homeAction(Request $request)
    {
        // Must be signed in
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        // Is this the first time since the account was created?
        $msgs = $request->getSession()->getFlashBag()->get(self::FLASHBAG_ACCOUNT_CREATED);
        if (count($msgs))
        {
            return $this->redirect('cerad_tourn_person_update');
        }
        
        $tplData = array();
        return $this->render('@CeradTourn/Tourn/Home/TournHomeIndex.html.twig', $tplData);
    }
}
