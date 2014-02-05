<?php
namespace Cerad\Bundle\GameBundle\Controller\GameOfficial\UserAssignSlot;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\FormInterface;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class UserAssignSlotController
{
    protected $router;
    protected $templating;
    
    public function __construct() {}
    
    public function setRouter    (RouterInterface $router)     { $this->router     = $router;     }
    public function setTemplating(EngineInterface $templating) { $this->templating = $templating; }
    
    /* =====================================================
     * Either assign or self assign
     * Model is injected, some checks have been made
     */
    public function assignAction(Request $request, UserAssignSlotModel $model, FormInterface $form)
    {   
        $form->handleRequest($request);

        if ($form->isValid())
        {   
            // Maybe try/catch
            $model->process($request->attributes);
            
            $formAction = $form->getConfig()->getAction();
            
            $redirectRoute = $request->attributes->get('_redirect');
            $redirectUrl   = $this->router->generate($redirectRoute);

            // Tack on game id, ref-sched-id should get passed in?
            return new RedirectResponse($redirectUrl . sprintf('#ref-sched-%d',$model->game->getNum()));
            
            return new RedirectResponse($formAction);
        }

        // And render, pass the model directly to the view?
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['game'] = $model->game;
        
        $tplName = $request->attributes->get('_template');
        
        return $this->templating->renderResponse($tplName,$tplData);
    }
}
