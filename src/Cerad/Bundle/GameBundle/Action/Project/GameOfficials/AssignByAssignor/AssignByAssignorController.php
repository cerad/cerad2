<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByAssignor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class AssignByAssignorController
{
    protected $router;
    protected $templating;
    
    public function __construct() {}
    
    public function setRouter    (RouterInterface $router)     { $this->router     = $router;     }
    public function setTemplating(EngineInterface $templating) { $this->templating = $templating; }
    
    public function assignAction(Request $request, AssignByAssignorModel $model, FormInterface $form)
    {   
        // Standard redirect, can't decide if the model should be able to generate these
        $redirectRoute = $request->attributes->get('_redirect');
      //$redirectUrl   = $this->router->generate($redirectRoute);
      //$redirectUrl  .= sprintf('#ref-sched-%d',$model->game->getNum());
        $redirectUrl = null;
        
        // Handle the form
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            // Maybe try/catch
            $model->process($request);

          //return new RedirectResponse($redirectUrl); // To schedule
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
        }

        // And render, pass the model directly to the view?
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['game'] = $model->game;
        
        $tplData['redirectUrl'] = $redirectUrl;
        
        $tplName = $request->attributes->get('_template');
        
        return $this->templating->renderResponse($tplName,$tplData);
    }
}
