<?php
namespace Cerad\Bundle\TournBundle\Controller\Schedule;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\TournBundle\FormType\Schedule\Official\AssignSlotFormType;

class ScheduleOfficialAssignController extends MyBaseController
{
    /* =====================================================
     * Either assign or self assign
     */
    public function assignAction(Request $request)
    {
        // The search model
        $model = $this->createModel($request);
        if (isset($model['response'])) return $model['response'];
        
        $form = $this->createModelForm($request,$model);
        
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            $model = $form->getData($model);
            
            $this->processModel($model);
            
            $route = $request->get('_route');
            return $this->redirect( $route,array('game' => $model['game']->getNum()));
        }
        else
        {
            $errors = $form->getErrors();
            //print_r($errors);
            //die('errors');
        }
        // And render
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['game'] = $model['game'];
        return $this->render($request->get('_template'),$tplData);
    }
    public function processModel($model)
    {   
        $personRepo = $this->get('cerad_person.person_repository');
         
        // Should point to original slots
        $slots = $model['slots'];
        foreach($slots as $slot)
        {
            $personGuid = $slot->getPersonGuid();
            if ($personGuid)
            {
                $person = $personRepo->findOneByGuid($personGuid);
                if ($person)
                {
                    $name = $person->getName();
                    $slot->setPersonNameFull($name->full);
                }
            }
            else
            {
                // TODO: Lookup person by full name
            }
        }
        // Lots to add
        $gameRepo = $this->get('cerad_game.game_repository');
        $gameRepo->commit();
        
    }
    public function createModel(Request $request)
    {   
        $model = array();

        $project = $this->getProject();
        
        $gameRepo = $this->get('cerad_game.game_repository');
        $game = $gameRepo->findOneByProjectNum($project->getId(),$request->get('game'));
        
        $officialSelect = $this->get('cerad_tourn.schedule_officials.select_s1games');
        $officials = $officialSelect->getOfficials($project->getId());
        
        $model['slot']      = $request->get('slot');
        $model['game']      = $game;
        $model['slots']     = $game->getOfficials();
        $model['project']   = $project;
        $model['officials'] = $officials; // List of available officials
        
        // Done
        return $model;
    }
    protected function createModelForm($request, $model)
    {
        $game = $model['game'];
        
        $builder = $this->createFormBuilder($model);
        
        $route = $request->get('_route');
        $builder->setAction($this->generateUrl($route,array('game' => $game->getNum())));
        $builder->setMethod('POST');
        
        $builder->add('slots','collection',array('type' => new AssignSlotFormType($model['officials'])));
        
        $builder->add('assign', 'submit', array(
            'label' => 'Assign Officials',
            'attr' => array('class' => 'submit'),
        ));        
         
        return $builder->getForm();
    }
}
