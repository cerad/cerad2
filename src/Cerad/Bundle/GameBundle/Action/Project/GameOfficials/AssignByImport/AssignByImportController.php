<?php

namespace Cerad\Bundle\GameBundle\Action\Project\GameOfficials\AssignByImport;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class AssignByImportController extends ActionController
{   
    public function action(Request $request, AssignByImportModel $model, FormInterface $form)
    {   
        $results = null;
        
        // Handle the form
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            // Maybe try/catch
            $results = $model->process($request);
            
            // No redirect here
            // $formAction = $form->getConfig()->getAction();
            // return new RedirectResponse($formAction);  // To form
        }

        // And render, pass the model directly to the view?
        $tplName = $request->attributes->get('_template');
        $tplData = array();
        $tplData['form']    = $form->createView();
        $tplData['results'] = $results;
        return $this->templating->renderResponse($tplName,$tplData);
    }
}
