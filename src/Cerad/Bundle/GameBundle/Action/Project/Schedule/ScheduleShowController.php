<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
//  Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ScheduleShowController extends ActionController
{
    public function action(Request $request, $model, $form)
    {
        $form->handleRequest($request);
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            
            $formAction = $form->getConfig()->getAction();
            
            return new RedirectResponse($formAction);
        }
        return;
    }
}
