<?php
namespace Cerad\Bundle\GameBundle\Action\Project\RefereeSchedule\Show;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class RefereeScheduleShowModel extends ActionModelFactory
{
    const SessionFormData = 'RefereeScheduleShow';
    
    public $project;
    public $criteria;
    public $formData;
    
    protected $gameRepo;
    
    public function __construct($gameRepo)
    {
        $this->gameRepo = $gameRepo;
    }
    public function create(Request $request)
    {   
        $this->project = $project = $request->attributes->get('project');
        die('Project ' . $project->getKey());
        
        $formData = array(
            'Fri' => true, 'Sat' => true, 'Sun' => true,
            'U10B' => true, 'U10G' => true,
            'U12B' => true, 'U12G' => true,
            'U14G' => true, 'U19G' => true,
        );
        
        $session = $request->getSession();
        if ($session->has(self::SessionFormData))
        {
            $sessionFormData = $session->get(self::SessionFormData);
            $formData = array_merge($formData,$sessionFormData);
        }
        $this->formData = $formData;
        return $this;
        
        $criteria = array();
        
        $criteria['projectKeys'] = array($project->getKey());
        
        $criteria['levels']  = array();
        $criteria['teams' ]  = array();
        $criteria['fields']  = array();
       
        $datesx = $project->getRawDates();
        $dates = array();
        foreach($datesx as $datex) $dates[] = $datex['date'];
        $criteria['dates'] = $dates;
            
      //print_r($criteria);die();
        
        $this->criteria = $criteria;
        return $this; 
        
    }
    public function process(Request $request, $formData)
    {
        $request->getSession()->set(self::SessionFormData,$formData);
    }
    public function loadGames()
    {
        $criteria = array();
        
        $criteria['projectKeys'] = array($this->project->getKey());
        
        if ($this->formData['Fri']) $criteria['dates'][] = '2014-04-25';
        if ($this->formData['Sat']) $criteria['dates'][] = '2014-04-26';
        if ($this->formData['Sun']) $criteria['dates'][] = '2014-04-27';
        
        if ($this->formData['U10B']) $criteria['levelKeys'][] = 'U10B';
        if ($this->formData['U10G']) $criteria['levelKeys'][] = 'U10G';
        if ($this->formData['U12B']) $criteria['levelKeys'][] = 'U12B';
        if ($this->formData['U12G']) $criteria['levelKeys'][] = 'U12G';
        if ($this->formData['U14G']) $criteria['levelKeys'][] = 'U14G';
        if ($this->formData['U19G']) $criteria['levelKeys'][] = 'U19G';
        
        $this->games = $this->gameRepo->queryGameSchedule($criteria);
        
        return $this->games;
    }
}
