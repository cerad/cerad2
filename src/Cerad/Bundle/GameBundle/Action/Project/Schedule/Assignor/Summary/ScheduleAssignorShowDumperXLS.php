<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Assignor\Summary;

use Cerad\Bundle\CoreBundle\Excel\ExcelDump;

class ScheduleAssignorSummaryDumperXLS extends ExcelDump
{
    protected $skipOnTimeChange = false;
    
    protected $phoneDataTransformer;
    protected $orgKeyDataTransformer;
    
    public function __construct($phoneDataTransformer,$orgKeyDataTransformer)
    {
        $this->phoneDataTransformer  = $phoneDataTransformer;
        $this->orgKeyDataTransformer = $orgKeyDataTransformer;
    }
    /* =======================================================================
     * Flatten the objects
     */
    protected function flatten($games)
    {
        $items = array();
        foreach($games as $game)
        {
            // Teams
            $homeTeam = $game->getHomeTeam();
            $awayTeam = $game->getAwayTeam();
            
            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            
            $time = $dt->format('G:i');   // 13:45
            $date = $dt->format('Y-m-d'); // yyyy-mm-dd
            
            $item = array(
                'dow'  => $dow,
                'num'  => $game->getNum(),
                'date' => $date,
                'time' => $time,
                
                'venue'    => $game->getVenueName(),
                'field'    => $game->getFieldName(),
                'groupKey' => $game->getGroupKey(),
                
                'homeTeamGroupSlot' => $homeTeam->getGroupSlot(),
                'awayTeamGroupSlot' => $awayTeam->getGroupSlot(),
            
                'homeTeamName' => $homeTeam->getTeamName(),
                'awayTeamName' => $awayTeam->getTeamName(),
                
                'RefereeName' => null,
                'AR1Name'     => null,
                'AR2Name'     => null
            );
            foreach($game->getOfficials() as $gameOfficial)
            {
                $role = $gameOfficial->getRole();
                
                $item[$role . 'Name'] = $gameOfficial->getPersonNameFull();
                
                $itemx = array(
                    'officialRole'        => $gameOfficial->getRole(),
                    'officialName'        => $gameOfficial->getPersonNameFull(),
                    'officialAssignRole'  => $gameOfficial->getAssignRole(),
                    'officialAssignState' => $gameOfficial->getAssignState(),
                    'officialBadge'       => $gameOfficial->getPersonBadge(),
                    'officialSAR'         => 
                        $this->orgKeyDataTransformer->transform($gameOfficial->getPersonOrgKey()),
                );
                $item[$role] = $itemx;
            }
            $items[] = $item;
        }
        return $items;
    }
    protected $mapGame = array(
        array('hdr' => 'Game', 'key' => 'num',  'width' =>  6, 'center' => true),
        array('hdr' => 'Date', 'key' => 'date', 'width' => 10),
        array('hdr' => 'DOW',  'key' => 'dow',  'width' =>  5, 'center' => true),
        array('hdr' => 'Time', 'key' => 'time', 'width' => 10),
        array('hdr' => 'Venue','key' => 'venue','width' => 18),
        array('hdr' => 'Field','key' => 'field','width' =>  8),
            
        array('hdr' => 'Group',  'key' => 'groupKey',          'width' => 22),
        array('hdr' => 'HT Slot','key' => 'homeTeamGroupSlot', 'width' => 10),
        array('hdr' => 'AT Slot','key' => 'awayTeamGroupSlot', 'width' => 10),
            
        array('hdr' => 'Home Team Name', 'key' => 'homeTeamName', 'width' => 26),
        array('hdr' => 'Away Team Name', 'key' => 'awayTeamName', 'width' => 26),
    );
    /* =======================================================================
     * Process each program
     */
    protected function dumpGames($ws,$items)
    {
        $mapOfficials = array(
            array('hdr' => 'Referee','key' => 'RefereeName', 'width' => 26),
            array('hdr' => 'AR1',    'key' => 'AR1Name',     'width' => 26),
            array('hdr' => 'AR2',    'key' => 'AR2Name',     'width' => 26),
        );
        $ws->setTitle('Games');
        
        $metas = array_merge($this->mapGame,$mapOfficials);
        
        $row = $this->setHeaders($ws,$metas);
        $timeCurrent = null;
        
        foreach($items as $item)
        {   
            $row++; $col = 0;
            foreach($metas as $meta)
            {
                // Skip on time changes
                if ($timeCurrent != $item['time'])
                {
                    if ($timeCurrent && $this->skipOnTimeChange) $row++;
                    $timeCurrent = $item['time'];
                }
                $ws->setCellValueByColumnAndRow($col++,$row,$item[$meta['key']]);
            }
        }        
    }
    protected function dumpSlots($ws,$items)
    {
        $mapOfficial = array(
            array('hdr' => 'SAR',     'key' => 'officialSAR',         'width' => 10),
            array('hdr' => 'State',   'key' => 'officialAssignState', 'width' =>  8),
            array('hdr' => 'Role',    'key' => 'officialRole',        'width' =>  8),
            array('hdr' => 'Official','key' => 'officialName',        'width' => 26),
            array('hdr' => 'Badge',   'key' => 'officialBadge',       'width' =>  8),
        );
        $ws->setTitle('Slots');
        
        $metas = array_merge($this->mapGame,$mapOfficial);
        
        $row = $this->setHeaders($ws,$metas);
        
        foreach($items as $item)
        {   
            foreach(array('Referee','AR1','AR2') as $role)
            {
                $item = array_merge($item,$item[$role]);
                
                $row++; $col = 0;
                foreach($metas as $meta)
                {
                    $ws->setCellValueByColumnAndRow($col++,$row,$item[$meta['key']]);
                }
            }
        }        
    }
    protected function dumpOfficials($ws,$games)
    {
        $items = array();
        foreach($games as $game)
        {
            foreach($game->getOfficials() as $official)
            {
                $name = $official->getPersonNameFull();
                if ($name)
                {
                    if (!isset($items[$name]))
                    {
                        $item = array(
                            'name'  => $name,
                            'total' => 0,
                            'Wed'   => 0,
                            'Thu'   => 0,
                            'Fri'   => 0,
                            'Sat'   => 0,
                            'Sun'   => 0,
                        );
                        $items[$name] = $item;
                    }
                    $item = $items[$name];
                    
                    $item['total']++;
                    
                    $dow  = $game->getDtBeg()->format('D');
                    $item[$dow]++;
                    
                    $item['email'] = $official->getPersonEmail();
                    $item['phone'] = $this->phoneDataTransformer->transform($official->getPersonPhone());
                    $item['skip']  = null;
                    
                    $items[$name] = $item;                    
                }
            }
        }
        ksort($items);
        
        $ws->setTitle('Referees');

        $metas = array(
            array('hdr' => 'Name','key' => 'name',  'width' => 26),
            array('hdr' => 'All', 'key' => 'total', 'width' =>  6),
            array('hdr' => 'Wed', 'key' => 'Wed',   'width' =>  6),
            array('hdr' => 'Thu', 'key' => 'Thu',   'width' =>  6),
            array('hdr' => 'Fri', 'key' => 'Fri',   'width' =>  6),
            array('hdr' => 'Sat', 'key' => 'Sat',   'width' =>  6),
            array('hdr' => 'Sun', 'key' => 'Sun',   'width' =>  6),
            
            array('hdr' => 'Skip',  'key' => 'skip',  'width' =>   6),
            array('hdr' => 'Email', 'key' => 'email', 'width' =>  26),
            array('hdr' => 'Phone', 'key' => 'phone', 'width' =>  26),
        );
        $row = $this->setHeaders($ws,$metas);
        
        foreach($items as $item)
        {   
            $row++; $col = 0;
            foreach($metas as $meta)
            {
                $ws->setCellValueByColumnAndRow($col++,$row,$item[$meta['key']]);
            }
        }
    }
    /* =======================================================================
     * Main entry point
     */
    public function dump($games)
    {
        $items = $this->flatten($games);
        
        // Spreadsheet
        $ss = $this->createSpreadsheet(); 
        
        // Games
        $ws0 = $this->createWorkSheet($ss,0);
        $this->dumpGames($ws0,$items);
        
        // By Slot
        $ws1 = $this->createWorkSheet($ss,1);
        $this->dumpSlots($ws1,$items);
        
        // By Referees
        $ws2 = $this->createWorkSheet($ss,2);
        $this->dumpOfficials($ws2,$games);
        
        // Output
        $ss->setActiveSheetIndex(2);
        return $this->getBuffer($ss);
    }
    public function setSkipOnTimeChange($value)
    {
        $this->skipOnTimeChane = $value;
    }
}
?>
