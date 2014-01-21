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
        $user   = $this->getUser();
        $person = $this->getUserPerson(true);
        
        /* ======================================================
         * This was an attempt to more or less force users to fill out their plans
         * The test for attending fails under the s1games lower tournament
         * Hack this for now
         * 
         * Need to add a havePlansBeenSet method to PersonPlan
         */
        $personPlan = $person->getPlan($project->getKey());
        if ($personPlan->getUpdatedOn() == null)
      //$basic = $personPlan->getBasic();
      //if (!isset($basic['attending'])  && !isset($basic['attendingLeague']))
        {
            return $this->redirect('cerad_tourn_person_plan_update');
        }
        // Good to go
        $tplData = array();
        $tplData['user']       = $user;
        $tplData['userPerson'] = $person;
        
        $tplData['project']   = $project;
        $tplData['fedRoleId'] = $project->getFedRoleId(); // AYSOV
        
        return $this->render('@CeradTourn/Tourn/Home/TournHomeIndex.html.twig', $tplData);
    }
}
