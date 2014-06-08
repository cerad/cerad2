<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\Person\Teams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class TeamsShowController extends ActionController
{   
    public function action(Request $request, $model, $form = null)
    {   
        // Handle the form
        if ($form) $form->handleRequest($request);

        if ($form && $form->isValid())
        {   
            // Maybe try/catch
            $results = $model->process($request);
            
            // No redirect here
            // $formAction = $form->getConfig()->getAction();
            // return new RedirectResponse($formAction);  // To form
        }

        // And render, pass the model directly to the view?
        $tplName = $model->_template;
        $tplData = array();
      //$tplData['form']    = $form->createView();
        return $this->regularResponse($tplName,$tplData);
    }
}
