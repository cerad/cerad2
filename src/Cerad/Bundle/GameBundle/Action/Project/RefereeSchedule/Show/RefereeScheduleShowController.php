<?php
namespace Cerad\Bundle\GameBundle\Action\Project\RefereeSchedule\Show;

use Cerad\Bundle\CoreBundle\Action\ActionController;

use Symfony\Component\HttpFoundation\Request;

class RefereeScheduleShowController extends ActionController
{
    public function action(Request $request, RefereeScheduleShowModel $model, $form)
    {
        $form->handleRequest($request);
        if ($form->isValid()) 
        {   
            $model->process($request,$form->getData());
            return $this->redirectResponse('cerad_tourn__referee_schedule__show');
        }

        $games = $model->loadGames();
        
        // And render
        $tplData = array();
        $tplData['searchForm'] = $form->createView();
        $tplData['games'] = $games;
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
