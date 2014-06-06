<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Util;

use Cerad\Component\Excel\Loader as BaseLoader;

class TeamsUtilReadXLS extends BaseLoader
{
    protected $projectKey;
    
    protected $record = array
    (
        'num'  => array('cols' => 'Team','req' => true),
        
        'levelKey' => array('cols' => 'Level',   'req' => true),
        
        'region' => array('cols' => 'Region', 'req' => true),
        'name'   => array('cols' => 'Name',   'req' => true),
        'points' => array('cols' => 'SfP',    'req' => true),

        'slot1' => array('cols' => 'Slots', 'req' => true),
        'slot2' => array('cols' => 'Slots', 'req' => true, 'plus' => 1),
        'slot3' => array('cols' => 'Slots', 'req' => true, 'plus' => 2),
        'slot4' => array('cols' => 'Slots', 'req' => true, 'plus' => 3),
        'slot5' => array('cols' => 'Slots', 'req' => true, 'plus' => 4),
    );
    public function __construct()
    {
        parent::__construct();
    }
    protected function processItem($item)
    {
        $num = (int)$item['num'];
        if (!$num) return;

        $team = array();
        $team['projectKey'] = $this->projectKey;
        $team['num']      = $num;
        $team['role']     = 'Physical';
        $team['status']   = 'Active';
        $team['sportKey'] = 'Soccer';
        
        $team['levelKey'] = $item['levelKey'];
        $team['region']   = $item['region'];
        $team['name']     = $item['name'];
        $team['points']   = $item['points'];
        
        $slots = array();
        for($i = 1; $i < 6; $i++)
        {
            $itemSlotKey = 'slot' . $i;
            if ($item[$itemSlotKey]) $slots[] = $item[$itemSlotKey];
        }
        $team['slots'] = $slots;
                
        $this->items[] = $team;
        
        return;
        
    }
    /* ==============================================================
     * Almost like the load but with a few tewaks
     */
    public function read($project,$filePath,$workSheetName = null)
    {
        $this->projectKey = $project->getKey();   
        
        return $this->load($filePath,$workSheetName);
    }
}
?>
