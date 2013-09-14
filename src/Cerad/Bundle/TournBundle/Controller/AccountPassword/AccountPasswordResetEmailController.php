<?php

namespace Cerad\Bundle\TournBundle\Controller\AccountPassword;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

/* ==============================================================
 * Base class for the request/requested controllers
 * Handles the email stuff
 */
class AccountPasswordResetEmailController extends MyBaseController
{
    public function getEmailModel($userId)
    {
        if (!$userId) return $this->redirect('cerad_tourn_welcome');
 
        $userManager = $this->get('cerad_user.user_manager');
        $user = $userManager->findUser($userId);
        
        if (!$user) return $this->redirect('cerad_tourn_welcome');
 
        $userToken = $user->getPasswordResetToken();
        if (!$userToken) return $this->redirect('cerad_tourn_welcome');
        
        $tplData = array();
        $tplData['user']      = $user;
        $tplData['userToken'] = $userToken;
        $tplData['prefix']    = 'ZaysoAdmin';
        
        $emailBody = $this->renderView(
            '@CeradTourn/AccountPassword/ResetEmail/AccountPasswordResetEmailBody.html.twig',  
            $tplData);
        
        $emailSubject = $this->renderView(
            '@CeradTourn/AccountPassword/ResetEmail/AccountPasswordResetEmailSubject.html.twig',
            $tplData);
        
        $model = array();
        $model['emailBody']    = $emailBody;
        $model['emailSubject'] = $emailSubject; 
        
        return $model;
    }
}
?>
