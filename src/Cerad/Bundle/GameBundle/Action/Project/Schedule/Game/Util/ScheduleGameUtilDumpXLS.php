<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Util;

use Cerad\Bundle\CoreBundle\Excel\ExcelDump;

class ScheduleGameUtilDumpXLS extends ExcelDump
{
    /* =======================================================================
     * Process each program
     */
    protected function dumpGames($ws,$games,$dumpOfficials)
    {
        $map = array(
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
        $mapOfficials = array(
            array('hdr' => 'Referee','key' => 'referee', 'width' => 26),
            array('hdr' => 'AR1',    'key' => 'ar1',     'width' => 26),
            array('hdr' => 'AR2',    'key' => 'ar2',     'width' => 26),
        );
        $ws->setTitle('Games');
        
        if ($dumpOfficials) $map = array_merge($map,$mapOfficials);
        
        $row = $this->setHeaders($ws,$map);
        $timeCurrent = null;
        
        foreach($games as $game)
        {   
            $row++;
            $col = 0;
            
            // Teams
            $homeTeam = $game->getHomeTeam();
            $awayTeam = $game->getAwayTeam();
            
            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            
            $time = $dt->format('G:i');   // 13:45
            $date = $dt->format('Y-m-d'); // yyyy-mm-dd
            
            // Skip on time changes
            if ($timeCurrent != $time)
            {
                if ($timeCurrent) $row++;
                $timeCurrent = $time;
            }
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
            $ws->setCellValueByColumnAndRow($col++,$row,$date);
            $ws->setCellValueByColumnAndRow($col++,$row,$dow);
            $ws->setCellValueByColumnAndRow($col++,$row,$time);
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getVenueName());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getFieldName());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getGroupKey());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$homeTeam->getGroupSlot());
            $ws->setCellValueByColumnAndRow($col++,$row,$awayTeam->getGroupSlot());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$homeTeam->getName());
            $ws->setCellValueByColumnAndRow($col++,$row,$awayTeam->getName());
            
            if (!$dumpOfficials) return;
            
            $officials = $game->getOfficials();
            foreach($officials as $official)
            {
                $ws->setCellValueByColumnAndRow($col++,$row,$official->getPersonNameFull());
            }
        }        
    }
    /* =======================================================================
     * Main entry point
     */
    public function dump($games,$dumpOfficials = false)
    {
        // Spreadsheet
        $ss = $this->createSpreadsheet(); 
        $ws = $this->createWorkSheet($ss,0);
        
        $this->dumpGames($ws,$games,$dumpOfficials);
        
        // Output
        $ss->setActiveSheetIndex(0);
        return $this->getBuffer($ss);
    }
}
?>
