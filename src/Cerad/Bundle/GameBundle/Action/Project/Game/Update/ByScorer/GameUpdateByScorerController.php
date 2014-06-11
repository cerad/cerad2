<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Game\Update\ByScorer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class GameUpdateByScorerController extends ActionController
{   
    public function action(Request $request, $model, FormInterface $form)
    {   
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
        
        $tplName = $request->attributes->get('_template');
        
        return $this->templating->renderResponse($tplName,$tplData);
    }
}
