<?php
namespace Cerad\Bundle\TournAdminBundle\Controller\PersonPlan;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\UserFormType;
use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\PersonFormType;
use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\PersonPlanFormType;

use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO\VolFormType;
use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO\RegionFormType;
use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO\RefereeCertFormType;
use Cerad\Bundle\TournAdminBundle\FormType\PersonPlan\Update\AYSO\SafeHavenCertFormType;

/* =================================================================
 * Currently only the pool play games are exported
 * But eventually want to add playoffs/champions as well
 */
class PersonPlanUpdateController extends MyBaseController
{
    public function updateAction(Request $request)
    {
        // Simple model
        $model = $this->createModel($request);
        if ($model['response']) return $model['response'];
        
        $form = $this->createModelForm($request,$model);
        
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            $model = $form->getData($model);
            
            $this->processModel($model);
            
            $route = $request->get('_route');
            return $this->redirect( $route,array('person' => $model['person']->getId()));
        }
        // And render
        $tplData = array();
        $tplData['form']   = $form->createView();
        $tplData['person'] = $model['person'];
        return $this->render($request->get('_template'),$tplData);                
    }
    protected function processModel($model)
    {
        $personRepo = $this->get('cerad_person.person_repository');
        
        // See if the aysoid has changed
        if ($model['fedKey'] != $model['personFed']->getFedKey())
        {
            // TODO: Need to see if changes fedKey already exists
            // If so then assorted logic
            $newFedKey = $model['personFed']->getFedKey();
            $existingPersonFed = $personRepo->findFedByFedKey($newFedKey);
            if ($existingPersonFed)
            {
                die('AYSOID already exists');
            }
        }
        // Commit
        $personRepo->commit();
        
        // Do some stuff for the user as well
        $user = $model['user'];
        if ($user->getId())
        {
            // Commit it
            $userManager = $this->get('cerad_user.user_manager');
            $userManager->updateUser($user);
        }
        return;
    }
    /* ===============================================
     * Pull a big tree
     * Want to flatten it?
     */
    protected function createModel(Request $request)
    {
        // Back and forth on this
        $model = array();
        $model['response'] = null;
        
        // Need current project
        $project = $this->getProject();
        $model['project'] = $project;
                
        // Person of interest
        $personRepo = $this->get('cerad_person.person_repository');
        $personId = $request->get('person');
        $person = $personRepo->find($personId);
        if (!$person)
        {
            $model['repsonse'] = $this->redirect('cerad_tourn_welcome');
            return $model;
        }
        $model['person'] = $person;
        
        // Any account
        $userRepo = $this->get('cerad_user.user_repository');
        $user = $userRepo->findOneByPersonGuid($person->getGuid());
        if (!$user) $user = $userRepo->createUser();
        $model['user'] = $user;
        
        // The plan
        $plan = $person->getPlan($project->getId());
        $model['plan'] = $plan;
        
        // The fed
        $personFed     = $person->getFed($project->getFedRole());
        $certReferee   = $personFed->getCertReferee();
        $certSafeHaven = $personFed->getCertSafeHaven();
        
        $model['personFed']     = $personFed;
        $model['certReferee']   = $certReferee;
        $model['certSafeHaven'] = $certSafeHaven;
        
        // Because changing this requires extra effort
        $model['fedKey'] = $personFed->getFedKey();
        
        // Done
        return $model;
    }
    /* =============================================================
     * For now we break things up into individual components
     * Makes it easier to customize for a given tournamnt
     * Might merge some of it later
     */
    protected function createModelForm($request, $model)
    {
        $person = $model['person'];
        
        $builder = $this->createFormBuilder($model);
        
        $route = $request->get('_route');
        $builder->setAction($this->generateUrl($route,array('person' => $person->getId())));
        $builder->setMethod('POST');
        
        $builder->add('user',          new UserFormType());
        $builder->add('person',        new PersonFormType());
        $builder->add('personFed',     new VolFormType());
        $builder->add('plan',          new PersonPlanFormType());
        $builder->add('certReferee',   new RefereeCertFormType());
        $builder->add('certSafeHaven', new SafeHavenCertFormType());
        
        $builder->add('update', 'submit', array(
            'label' => 'Update Person',
            'attr'  => array('class' => 'submit'),
        ));        
         
        return $builder->getForm();
    }
}
