<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourns;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournsWelcomeController extends MyBaseController
{
    public function welcomeAction(Request $request, $slug)
    {
      //if ($this->hasRoleUser() && !$this->hasRoleAdmin()) return $this->redirect('cerad_tourn_home');
        
        $projects = $this->getProjects();
        
        $tplData = array();
        $tplData['projects'] = $projects;
        return $this->render('@CeradTourn/Tourns/Welcome/TournsWelcomeIndex.html.twig', $tplData);
    }
}
