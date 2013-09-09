<?php
namespace Cerad\Bundle\TournBundle\Controller\Tourn;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class TournUserInfoController extends MyBaseController
{
    public function renderAction(Request $request)
    {
        $tplData['user']    = $this->getUser();
        $tplData['project'] = $this->getProject();
        
        // Guest
        if (!$this->hasRoleUser())
        {
            return $this->render('@CeradTourn/Tourn/UserInfo/TournGuestInfo.html.twig',$tplData);
        }
        $tplData['user'] = $this->getUser();
        
        // Have a person?
        
        // Regular user
        if (!$this->hasRoleAdmin())
        {
            return $this->render('@CeradTourn/Tourn/UserInfo/TournUserInfo.html.twig',$tplData);
        }
        
        // Admin
        return $this->render('@CeradTourn/Tourn/UserInfo/TournAdminInfo.html.twig',$tplData);
     }
}
