<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\PersonTeams\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class PersonTeamsShowController extends ActionController
{   
    public function action(Request $request, $model, $form = null)
    {   
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            $model->process($form->getData());
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);
        }
        $tplName = $model->_template;
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['_back'] = $model->_back;
        return $this->regularResponse($tplName,$tplData);
    }
}
