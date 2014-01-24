<?php
namespace Cerad\Bundle\TournBundle\Controller\Schedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\FormType\Schedule\Official\SelfAssignSlotFormType;

class ScheduleOfficialSelfAssignController
{
    protected $helper;
    protected $model;
    
    public function __construct($helper,$model)
    {
        $this->helper = $helper;
        $this->model  = $model;
    }
    /* =====================================================
     * Either assign or self assign
     */
    public function assignAction(Request $request)
    {
        // TODO: Inject model instead of creating it here
        // If model creation fails then we would never get here
        $model = $this->model; // $this->get($request->get('_model'));
        if (!$model->valid) 
        {
            return $this->helper->redirect($this->helper->generateUrl($request->get('_redirect')));
        }
        
        $form = $this->createForm($request,$model);
        
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            $response = $model->process();
            
            if ($response) return $response;
            
            return $this->redirect($model['_route'],array('game' => $model['game']->getNum()));
        }

        // And render, pass the model directly to the view?
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['game'] = $model->game;
        $tplName = $request->get('_template');
        return $this->helper->render($tplName,$tplData);
    }
    public function createForm($request,$model)
    {
        $game = $model->game;
        $slot = $model->slot;
        
        $builder = $this->helper->createFormBuilder($model);
        
        $builder->setAction($this->helper->generateUrl($request->get('_route'),
            array('game' => $game->getNum(),'slot' => $slot)
        ));
      //$builder->setMethod('POST'); // default
        
      //$builder->add('slots','collection',array('type' => new SelfAssignSlotFormType($model['officials'])));
        
        $builder->add('gameOfficial',new SelfAssignSlotFormType());
        
        $builder->add('assign', 'submit', array(
            'label' => 'Request Assignment',
            'attr' => array('class' => 'submit'),
        ));        
         
        return $builder->getForm();
    }
}
