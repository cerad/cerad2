<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\Persons\Show;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class PersonsShowController extends ActionController
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
        $tplData['form']    = $form->createView();
        $tplData['projectPersons'] = $model->loadProjectPersons();
        return $this->regularResponse($tplName,$tplData);
    }
}
