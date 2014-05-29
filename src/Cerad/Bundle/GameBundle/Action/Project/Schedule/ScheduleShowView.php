<?php
namespace Cerad\Bundle\GameBundle\Action\Project\Schedule;

use Cerad\Bundle\CoreBundle\Action\ActionView;

use Symfony\Component\HttpFoundation\Request;

class ScheduleShowView extends ActionView
{
    public function renderResponse(Request $request)
    {
        $form  = $request->attributes->get('form');
        $model = $request->attributes->get('model');
        
        $tplName = $request->attributes->get('_template');
        
        $tplData = array();
        $tplData['games'] = $model->loadGames();
        if ($form)
        {
            $tplData['searchForm'] = $form->createView();
        }
        return $this->templating->renderResponse($tplName,$tplData);
    }
}
