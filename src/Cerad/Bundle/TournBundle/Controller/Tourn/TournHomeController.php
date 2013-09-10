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
        
        // Always need project
        $project = $this->getProject();
        
        // Pass user and main userPerson to the listing
        $user = $this->getUser();
        $personId = $user->getPersonId();
        $personRepo = $this->get('cerad_person.person_repository');
        $person = $personRepo->find($personId);
        
        if (!$person) 
        {
            $person = $personRepo->createPerson();
            $person->getPersonPersonPrimary();
        }
        $tplData = array();
        $tplData['user']       = $user;
        $tplData['userPerson'] = $person;
        
        $tplData['project']   = $project;
        $tplData['project']   = $project;
        $tplData['fedRoleId'] = $project->getFedRoleId(); // AYSOV
        
        return $this->render('@CeradTourn/Tourn/Home/TournHomeIndex.html.twig', $tplData);
    }
}
