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
            // Always process?
            $formData = $form->getData();
            $model->process($request,$formData);
            
            $gameNum = $model->game->getNum();
            
            if ($form->get('next')->isClicked())
            {
                $gameNum = $formData['nextGameNum'];
            }
            $actionUrl = $this->generateUrl(
                'cerad_game__project__game_report__update',
                array(
                    '_project' => $model->_project,
                    '_game'    => $gameNum,
                    'back'     => $model->back,
                ));
          //$formAction = $form->getConfig()->getAction();
            return new RedirectResponse($actionUrl);  // To form
        }   
        $tplData = array();
        $tplData['form']       = $form->createView();
        $tplData['formErrors'] = $form->getErrors();
        $tplData['game']       = $model->game;
        $tplData['back']       = $model->back;
        
        return $this->regularResponse($request->get('_template'),$tplData);
    }
}
