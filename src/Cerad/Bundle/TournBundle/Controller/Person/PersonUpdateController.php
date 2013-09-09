<?php
namespace Cerad\Bundle\TournBundle\Controller\Person;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

class PersonUpdateController extends MyBaseController
{
    public function updateAction(Request $request, $id = 0)
    {
        // Document
        $personId = $id;
        $project = $this->getProject();
        
        // Security
        if (!$this->hasRoleUser()) { return $this->redirect('cerad_tourn_welcome'); }
        
        // Simple model
        $model = $this->createModel($project,$personId);

        $form = $this->createFormForModel($project,$model);
        
        $tplData = array();
        $tplData['form']    = $form->createView();
        $tplData['person']  = $model['person'];
        $tplData['project'] = $project;
        return $this->render('@CeradTourn/Person/Update/PersonUpdateIndex.html.twig', $tplData);
    }
    /* ===============================================
     * Model is just the person
     */
    protected function createModel($project,$personId)
    {
        // Always want project
        $model = array();
        
        // Get the person
        $personRepo = $this->get('cerad_person.person_repository');
        $person = null;
        
        // If passed an id then use it
        if ($personId)
        {
            $person = $personRepo->find($personId);
        }
        
        // Use the account person
        if (!$person)
        {
            $user = $this->getUser();
            $personId = $user->getPersonId();
            $person = $personRepo->find($personId);
        }
        if (!$person)
        {
            throw new \Exception('No person in cerad_tourn_person_edit');
        }
        $personFed = $person->findFed($project->getFedRoleId());
 
        //$personOrg = $personFed->getOrgState();
        //$personCertRef = $personFed->getCertReferee();
        
        // Simple model
        $model['person']    = $person;
        $model['fedId']     = $personFed->getId();
      //$model['orgId']     = $personOrg->getOrgId();
      //$model['badge']     = $personCertRef->getBadgex();
        $model['upgrading'] = 'No';
        
        // Value object
        $name = $person->getName();
        $model['personName']      = $name;
        $model['personNameFull']  = $name->full;
        $model['personNameFirst'] = $name->first;
        $model['personNameLast']  = $name->last;
        $model['personNameNick']  = $name->nick;
         
        $model['personEmail'] = $person->getEmail();
        $model['personPhone'] = $person->getPhone();
        
        return $model;
    }
    /* ==========================================
     * Hand crafted form
     */

    public function createFormForModel($project,$model = null)
    {
        $fedRoleId = $project->getFedRoleId();
        $fedIdTypeService  = sprintf('cerad_person_%s_id_fake',strtolower($fedRoleId));
 
      //$orgIdType     = $this->get('cerad_person.ussf_org_state.form_type');
      //$badgeType     = $this->get('cerad_person.ussf_referee_badge.form_type');
      //$upgradingType = $this->get('cerad_person.ussf_referee_upgrading.form_type');
        
        $formOptions = array(
          //'validation_groups'  => array('basic'),
            'cascade_validation' => true,
        );
        $constraintOptions = array();
        
        $builder = $this->createFormBuilder($model,$formOptions);
        
        $builder->add('fedId',$fedIdTypeService, array(
            'required' => false,
            'disabled' => true,
        ));
       
        $builder->add('personNameFull','text', array(
            'required' => true,
            'label'    => 'Full Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('personNameFirst','text', array(
            'required' => true,
            'label'    => 'First Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personNameLast','text', array(
            'required' => true,
            'label'    => 'Last Name',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personNameNick','text', array(
            'required' => false,
            'label'    => 'Nick Name',
            'trim'     => true,
            'constraints' => array(
            ),
            'attr' => array('size' => 20),
        ));
        $builder->add('personEmail','email', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
                new EmailConstraint   ($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
        $builder->add('personPhone','text', array(
            'required' => false,
            'label'    => 'Cell Phone',
            'trim'     => true,
            'constraints' => array(
            ),
            'attr' => array('size' => 20),
        ));
          
/*
            ->add('badge',    $badgeType)
            ->add('orgId',    $orgIdType)
            ->add('upgrading',$upgradingType)
        ;*/
        return $builder->getForm();
    }
}
