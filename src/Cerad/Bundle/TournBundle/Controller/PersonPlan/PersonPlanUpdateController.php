<?php
namespace Cerad\Bundle\TournBundle\Controller\PersonPlan;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\TournBundle\FormType\DynamicFormType;

/* ========================================================
 * Person Plan Editor
 * Passing the person for now because might end up with people with no plans
 */
class PersonPlanUpdateController extends MyBaseController
{   
    public function updateAction(Request $request, $id = null)
    {
        // Document
        $personId = $id;
        $project = $this->getProject();
        
        // Security
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        // Simple model
        $model = $this->createModel($project,$personId);
                      
        // This could also be passed in
        $form = $this->createModelForm($project,$model);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $model1 = $form->getData();
            
            $model2 = $this->processModel($project,$model1);
            
            // Notify email system
            $person2 = $model2['person'];
            $this->redirect('cerad_tourn_person_plan_update',array('id' => $person2->getId()));
            
        }

        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        
        $tplData['plan'   ] = $model['plan'];
        $tplData['person' ] = $model['person'];
        $tplData['project'] = $project;

        return $this->render('@CeradTourn\PersonPlan\Update\PersonPlanUpdateIndex.html.twig',$tplData);        
    }
     
    protected function createModel($project,$personId)
    {   
        $personRepo = $this->get('cerad_person.person_repository');
        $person = null;
        
        // If passed a plan then use it
        if ($personId) $person = $personRepo->find($personId);
        else
        {
            $user = $this->getUser();
            $personId = $user->getPersonId();
            $person = $personRepo->find($personId);
        }
        if (!$person) throw new \Exception('Person not found in lan update');
        
        $plan = $person->getPlan($project->getId());
        $plan->mergeBasicProps($project->getBasic());
        
        // Pack it up
        $model = array();
        $model['plan'  ] = $plan;
        $model['basic' ] = $plan->getBasic();
        $model['notes' ] = $plan->getNotes();
        $model['person'] = $person;
        
        return $model;
    }
    protected function createModelForm($project, $model)
    {   
        $basicType = new DynamicFormType('basic',$project->getBasic());
        
        $formOptions = array(
            'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
                
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->add('basic',$basicType, array('label' => false));
        
/* ==============================
 * Does not quit work
        $builder->add('notes','textarea', array(
            'label' => false,
            'required' => false,
            'attr' => array('cols' => 50, 'rows' => 5)
        ));
        */
        return $builder->getForm();
    }
    /* ===============================================
     * Lot's of possible processing to do
     * All ends with a plan
     */
    protected function processModel($project,$model)
    {
        $personRepo = $this->get('cerad_person.person_repository');
         
        // Unpack dto
        $plan   = $model['plan'];
        $basic  = $model['basic'];
        $notes  = $model['notes'];
        $person = $model['person'];
        
        $plan->setBasic($basic);
        $plan->setNotes($notes);
                
        // And save
        $personRepo->save($person);
        $personRepo->commit();
       
        return $model;
    }

    public function registerActionx(Request $request, $slug, $op = null)
    {
        // Get the project
        $projectRepo = $this->get('cerad_tourns.project.repository');
        $project = $projectRepo->findBySlug($slug);
        if (!$project) return $this->redirect($this->generateUrl('cerad_tourns_welcome'));
               
        // This could be passed in or pull from a dispatch?
        $dto = $this->createDto($request,$project);
                        
        // This could also be passed in
        $form = $this->createFormBuilderDto($dto)->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) 
        {             
            // Maybe dispatch something to adjust form
            $dto = $form->getData();
            
            // Handled with a dispatch
            $this->processDto($dto);
            
            // Send processedDto message to kick off email?
            
            // Store plan id in session
            //$plan = $dto['plan'];die('Plan ' . self::SESSION_PLAN_ID . ' ' . $plan->getId());
            //$request->getSession()->set(self::SESSION_PLAN_ID, $plan->getId());
            
            //return $this->redirect($this->generateUrl('cerad_tourns_project',array('slug' => $slug)));
        }
        
        // Template stuff
        $tplData = array();
        $tplData['msg'    ] = null; // $msg; from flash bag
        $tplData['form'   ] = $form->createView();
        $tplData['project'] = $project;

        return $this->render('CeradTournsBundle:Register:index.html.twig',$tplData);        
    }
    protected function sendRefereeEmail($tourn,$plans)
    {   
        $prefix = $tourn['prefix']; // OpenCup2013
        
        $assignorName  = $tourn['assignor']['name'];
        $assignorEmail = $tourn['assignor']['email'];
        
      //$assignorEmail = 'ahundiak@nasoa.org';
        
        $adminName =  'Art Hundiak';
        $adminEmail = 'ahundiak@gmail.com';
        
        $refereeName  = $plans->getPerson()->getFirstName() . ' ' . $plans->getPerson()->getLastName();
        $refereeEmail = $plans->getPerson()->getEmail();
        
        $tplData = $tourn;
        $tplData['plans'] = $plans; 
        $body = $this->renderView('CeradTournBundle:Tourn:email.txt.twig',$tplData);
    
        $subject = sprintf("[%s] Ref App %s",$prefix,$refereeName);
       
        // This goes to the assignor
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setFrom(array('admin@zayso.org' => $prefix));
        $message->setBcc (array($adminEmail => $adminName));
        
        $message->setTo     (array($assignorEmail  => $assignorName));
        $message->setReplyTo(array($refereeEmail   => $refereeName));

        $this->get('mailer')->send($message);
      //return;
        
        // This goes to the referee
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setFrom(array('admin@zayso.org' => $prefix));
      //$message->setBcc (array($adminEmail => $adminName));
        
        $message->setTo     (array($refereeEmail  => $refereeName));
        $message->setReplyTo(array($assignorEmail => $assignorName));

        $this->get('mailer')->send($message);
    }
}
?>
