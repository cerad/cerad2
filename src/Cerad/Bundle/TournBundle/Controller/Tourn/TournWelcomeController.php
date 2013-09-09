<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournWelcomeController extends MyBaseController
{
    public function welcomeAction(Request $request)
    {
        if ($this->hasRoleUser() && !$this->hasRoleAdmin()) return $this->redirect('cerad_tourn_home');
        
        $tplData = array();
        return $this->render('@CeradTourn/Tourn/Welcome/TournWelcomeIndex.html.twig', $tplData);
    }
}
