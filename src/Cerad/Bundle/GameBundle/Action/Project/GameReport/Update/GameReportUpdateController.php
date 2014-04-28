<?php
namespace Cerad\Bundle\GameBundle\Action\Project\GameReport\Update;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GameReportUpdateController extends ActionController
{
    public function action(Request $request, RefereeScheduleShowModel $model, $form)
    {
        die('game report update controller');
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
