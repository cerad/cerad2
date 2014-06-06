<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class GameUpdateByScorerController extends ActionController
{   
    public function updateAction(Request $request, $model, FormInterface $form)
    {   
        // Standard redirect, can't decide if the model should generate these
        $redirectRoute = $request->attributes->get('_redirect');
        $redirectUrl   = $this->router->generate($redirectRoute);
        $redirectUrl  .= sprintf('#sched-%d',$model->game->getNum()); // Class prefix needs to be injected

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
