<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class AssignByAssignorController extends ActionController
{   
    public function __construct() {}
    
    public function assignAction(Request $request, AssignByAssignorModel $model, FormInterface $form)
    {   
        // Handle the form
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            // Maybe try/catch
            $model->process($request);
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
        }

        // And render, pass the model directly to the view?
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['game'] = $model->game;
        $tplData['back'] = $model->back;
        
        $tplName = $request->attributes->get('_template');
        
        return $this->templating->renderResponse($tplName,$tplData);
    }
}
