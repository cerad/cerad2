<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Referee\Show;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ScheduleRefereeShowController extends ActionController
{
    public function action(Request $request, ScheduleRefereeShowModel $model, $form)
    {
        $form->handleRequest($request);
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            
            $formAction = $form->getConfig()->getAction();
            return new RedirectResponse($formAction);  // To form
         }

        $games = $model->loadGames();
        
        // And render
        $tplData = array();
        $tplData['searchForm'] = $form->createView();
        $tplData['games'] = $games;
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
