<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GameReportUpdateController extends ActionController
{
    public function action(Request $request, GameReportUpdateModel $model, $form)
    {
        $form->handleRequest($request);
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
        }   
        $tplData = array();
        $tplData['form']       = $form->createView();
        $tplData['formErrors'] = $form->getErrors();
        $tplData['game']       = $model->game;
        $tplData['back']       = $model->back;
        
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
