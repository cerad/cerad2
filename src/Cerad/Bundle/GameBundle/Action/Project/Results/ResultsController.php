<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Results;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
//  Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResultsController extends ActionController
{
    public function action(Request $request, $model, $form = null)
    {
        // Just in case we do have a form
        if (!$form) return;
        die('Results Controller with form');
        $form->handleRequest($request);
        
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            
            $formAction = $form->getConfig()->getAction();
            
            return new RedirectResponse($formAction);
        }
    }
}
