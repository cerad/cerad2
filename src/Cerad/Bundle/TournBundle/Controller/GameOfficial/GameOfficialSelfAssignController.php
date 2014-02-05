<?php
namespace Cerad\Bundle\TournBundle\Controller\GameOfficial;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\GameBundle\FormType\GameOfficial\SelfAssignSlotFormType;

class UserAssignController
{
    protected $actionHelper;
    
    public function __construct($actionHelper)
    {
        $this->actionHelper = $actionHelper;
    }
    /* =====================================================
     * Either assign or self assign
     * Model is injected, some checks have been made
     */
    public function assignAction(Request $request, $model)
    {   
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
        $tplName = $request->attributes->get('_template');
        return $this->actionHelper->render($tplName,$tplData);
    }
    public function createForm($request,$model)
    {
        $game = $model->game;
        $slot = $model->slot;
        
        $builder = $this->actonHelper->createFormBuilder($model);
        
        $builder->setAction($this->actionHelper->generateUrl($request->get('_route'),
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
