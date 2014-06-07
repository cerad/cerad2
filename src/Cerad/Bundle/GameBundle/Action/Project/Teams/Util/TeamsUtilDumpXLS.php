<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Util;

use Cerad\Bundle\CoreBundle\Excel\ExcelDump;

class TeamsUtilDumpXLS extends ExcelDump
{
    /* =======================================================================
     * Dump Team
     */
    protected function dumpTeam($ws,$model,$program,$team,&$row)
    {
        /* =============================================
         * Used for conversion
        $region = $team->getOrgKey();
        if (!$region && false)
        {
            $region = $team->getName();
            if (strpos($region,'Team ') === 0) $region = null;
        }
        */
        $col = 0;
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getLevelKey());
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getNum());
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getOrgKey());
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getName());
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getPoints());
        
        $gameTeams = $model->findAllGameTeamsByTeam($team);
        $slots = array();
        foreach($gameTeams as $gameTeam)
        {
            $game = $gameTeam->getGame();
            $slot = sprintf('%s:%s:%s',$game->getGroupType(),$game->getGroupName(),$gameTeam->getGroupSlot());
            if (!isset($slots[$slot]))
            {
                $ws->setCellValueByColumnAndRow($col++,$row,$slot);
                $slots[$slot] = true;
            }
        }
        $row++;
    }
    /* =======================================================================
     * Dump each program
     */
    protected function dumpProgram($ss,&$sheetNum,$model,$program)
    {
        $map = array(
            array('hdr' => 'Level', 'key' => 'levelKey','width' => 20 ),
            array('hdr' => 'Team',  'key' => 'num',     'width' =>  6, 'center' => true),
            array('hdr' => 'Region','key' => 'orgKey',  'width' => 12 ),
            array('hdr' => 'Name',  'key' => 'name',    'width' => 20 ),
            array('hdr' => 'SfP',   'key' => 'points',  'width' =>  4, 'center' => true ),
            array('hdr' => 'Slots', 'key' => null,      'width' => 16 ),
            
            array('hdr' => 'Fri U10PP Or QF', 'key' => null, 'width' => 16 ),
            array('hdr' => 'Sat U10PP Or SF', 'key' => null, 'width' => 16 ),
            array('hdr' => 'Sun          FM', 'key' => null, 'width' => 16 ),
        );
        
        // Landscape formatted worksheet
        $ws = $this->createWorkSheet($ss,$sheetNum++);
        
        $ws->setTitle($program . ' Teams');
        $row = $this->setHeaders($ws,$map);
        
        $levelKey = null;
        $teams = $model->loadTeams($program);
        foreach($teams as $team)
        {
            if ($levelKey != $team->getLevelKey())
            {
                $row++;
                $levelKey = $team->getLevelKey();
            }
            $this->dumpTeam($ws,$model,$program,$team,$row);
        }        
    }
    /* =======================================================================
     * Main entry point
     */
    public function dump($model)
    {
        // Spreadsheet
        $ss = $this->createSpreadsheet(); 
        $sheetNum = 0;
        
        $programs = $model->getPrograms();
        foreach($programs as $program)
        {
            $this->dumpProgram($ss,$sheetNum,$model,$program);
        }
        $ss->setActiveSheetIndex(0);
        
        // Output
        return $this->getBuffer($ss);
    }
}
?>
