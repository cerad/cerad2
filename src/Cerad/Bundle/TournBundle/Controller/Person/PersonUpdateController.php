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

        $form = $this->createModelForm($project,$model);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model1 = $form->getData();
            
            $model2 = $this->processModel($project,$model1);
            $person2 = $model2['person'];
            
            return $this->redirect('cerad_tourn_home');
            return $this->redirect('cerad_tourn_person_update',array('id' => $person2->getId()));
        }
        
        $tplData = array();
        $tplData['form']    = $form->createView();
        $tplData['person']  = $model['person'];
        $tplData['project'] = $project;
        return $this->render('@CeradTourn/Person/Update/PersonUpdateIndex.html.twig', $tplData);
    }
    protected function processModel($project,$model)
    { 
        // Update person
        $person = $model['person'];
        $name = $person->getName();
        $name->full  = $model['personNameFull'];
        $name->first = $model['personNameFirst'];
        $name->last  = $model['personNameLast'];
        $name->nick  = $model['personNameNick'];
        $person->setName($name);
        
        $person->setEmail($model['personEmail']);
        $person->setPhone($model['personPhone']);
        
        // Certs
      //$fedId     = $model['fedId'    ];
        $orgId     = $model['orgId'    ];
        $badge     = $model['badge'    ];
        $upgrading = $model['upgrading'];
        
        $personFed     = $person->getFed($project->getFedRoleId());
        $personOrg     = $personFed->getOrg();
        $personCertRef = $personFed->getCertReferee();
        
        $personOrg->setOrgId($orgId);
        $personCertRef->setBadgex($badge);
        $personCertRef->setUpgrading($upgrading);
        
        // And persist
        $personRepo = $this->get('cerad_person.person_repository');
        $personRepo->save($person);
        $personRepo->commit();
        
        // Done
        return $model;
    }
    /* ===============================================
     * Person + cert + org
     */
    protected function createModel($project,$personId)
    {
        // Always want project
        $model = array();
        
        // Get the person
        $personRepo = $this->get('cerad_person.person_repository');
        $person = null;
        
        // If passed an id then use it
        if ($personId) $person = $personRepo->find($personId);
        
        // Use the account person
        if (!$person) $person = $this->getUserPerson(false);
        if (!$person)
        {
            throw new \Exception('No person in cerad_tourn_person_edit');
        }
        $personFed = $person->getFed($project->getFedRoleId());
 
        $personOrg     = $personFed->getOrg();
        $personCertRef = $personFed->getCertReferee();
        
        // Simple model
        $model['person']    = $person;
        $model['fedId']     = $personFed->getFedId();
        $model['orgId']     = $personOrg->getOrgId();
        $model['badge']     = $personCertRef->getBadgex();
        $model['upgrading'] = $personCertRef->getUpgrading();
        
        // Value object, just flatten for now
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

    public function createModelForm($project,$model = null)
    {
        $fedRoleId = $project->getFedRoleId();
        
        // Service id's are not case sensitive
        $fedIdTypeServiceId = sprintf('cerad_person.%s_id_Fake.form_type',      $fedRoleId);
        $orgIdTypeServiceId = sprintf('cerad_person.%s_org_id.form_type',       $fedRoleId);
        $badgeTypeServiceId = sprintf('cerad_person.%s_referee_badge.form_type',$fedRoleId);
        
        $fedIdTypeService   = $this->get($fedIdTypeServiceId);
        $orgIdTypeService   = $this->get($orgIdTypeServiceId);
        $badgeTypeService   = $this->get($badgeTypeServiceId);
        
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
        $builder->add('orgId',$orgIdTypeService, array(
            'required' => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
        )));
        $builder->add('badge',$badgeTypeService, array(
            'required' => true,
        ));
        $builder->add('fedId',$fedIdTypeService, array(
            'required' => false,
            'disabled' => true,
        ));
        $builder->add('upgrading','cerad_person_upgrading', array(
            'required' => false,
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
        $builder->add('personPhone','cerad_person_phone', array(
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
