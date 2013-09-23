<?php
namespace Cerad\Bundle\TournBundle\Controller\Schedule;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

class ScheduleOfficialListController extends MyBaseController
{
    const SESSION_SCHEDULE_OFFICIAL_SEARCH = 'cheduleOfficialSearch';
    
    /* =====================================================
     * Wanted to just use GET but the dates mess up
     * Use the session trick for now
     */
    public function listAction(Request $request)
    {
        // The search model
        $model = $this->getModel($request);
        
        // The form stuff
        $searchFormType = $this->get('cerad_tourn.schedule_official_search.form_type');
        $searchForm = $this->createForm($searchFormType,$model);
        
        $searchForm->handleRequest($request);

        if ($searchForm->isValid()) // GET Request
        {   
            $modelPosted = $searchForm->getData();
            
            $request->getSession()->set(self::SESSION_SCHEDULE_OFFICIAL_SEARCH,$modelPosted);
            
            return $this->redirect('cerad_tourn_schedule_official_list');
        }

        // Query for the games
        $gameRepo = $this->get('cerad_game.game_repository');
        $games = $gameRepo->queryGameSchedule($model);
        
        // And render
        $tplData = array();
        $tplData['searchForm'] = $searchForm->createView();
        $tplData['games']   = $games;
        $tplData['isAdmin'] = false;
        $tplData['project'] = $this->getProject();
        return $this->render('@CeradTourn/Schedule/Official/ScheduleOfficialIndex.html.twig',$tplData);
    }
    public function getModel(Request $request)
    {   
        $model = array();

        $project = $this->getProject();
        $model['projects'] = array($project->getId());
        
        $model['teams' ]  = array();
        $model['fields']  = array();
        
        $model['searches'] = $searches = $project->getSearches();
        
        foreach($searches as $name => $search)
        {
            $model[$name] = $search['default']; // Array of defaults
        }
      //print_r($model['levelIds']); die();
        
        // Merge form session
        $session = $request->getSession();
        if ($session->has(self::SESSION_SCHEDULE_OFFICIAL_SEARCH))
        {
            $modelSession = $session->get(self::SESSION_SCHEDULE_OFFICIAL_SEARCH);
            $model = array_merge($model,$modelSession);
        }
    
        // Done
        return $model;
    }
}
