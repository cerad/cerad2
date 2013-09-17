<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournUserInfoController extends MyBaseController
{
    public function renderAction(Request $request)
    {
        $tplData = array();
        
        $project = $this->getProject();
        
        $tplData['project'] = $project;
        
        // Guest
        if (!$this->hasRoleUser())
        {
            return $this->render('@CeradTourn/Tourn/UserInfo/TournGuestInfo.html.twig',$tplData);
        }
        
        // Pass user and main userPerson to the listing
        $user   = $this->getUser();
        $person = $this->getUserPerson(true);
        
        $personFed = $person->getFed($project->getFedRoleId());
        
        $tplData['user']      = $user;
        $tplData['person']    = $person;
        $tplData['personFed'] = $personFed;

        // Regular user
        if (!$this->hasRoleAdmin())
        {
            return $this->render('@CeradTourn/Tourn/UserInfo/TournUserInfo.html.twig',$tplData);
        }
        
        // Admin
        return $this->render('@CeradTourn/Tourn/UserInfo/TournAdminInfo.html.twig',$tplData);
     }
}
