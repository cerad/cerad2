<?php
namespace Cerad\Bundle\TournBundle\Schedule\Officials\Select;

/* ===========================================================
 * Probably getting too complicated here but the fact that the personPlan object
 * can be customized for each tournament makes it difficult to determine who wants to referee
 * willReferee vs referering etc
 * willAttend  vs attending
 * 
 * Also a possibility that we might want to filter based on game level, availability etc
 */
class ScheduleOfficialsSelectS1Games
{
    public function __construct($personRepo)
    {
        $this->personRepo = $personRepo;
    }
    public function isOfficial($projectId,$person)
    {
        $plan = $person->getPlan($projectId,false);
        if (!$plan) return false;
        
        $basic = $plan->getBasic();
        
        $willAttend  = isset($basic['attending' ]) ? $basic['attending']  : null;
        if ($willAttend != 'yes') return false;
        
        $willReferee = isset($basic['refereeing']) ? $basic['refereeing'] : null;
        if ($willReferee != 'yes') return false;
        
        return true;
    }
    public function getOfficials($projectId)
    {
        $officials = array();
        $persons = $this->personRepo->query(array($projectId));
        
        foreach($persons as $person)
        {
            if ($this->isOfficial($projectId,$person))
            {
                $officials[$person->getGuid()] = $person->getName()->full;
            }
        }
        return $officials;
    }
}
?>
