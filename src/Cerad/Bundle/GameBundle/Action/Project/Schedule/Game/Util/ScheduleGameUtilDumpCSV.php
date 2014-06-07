<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Util;

/* ============================================
 * Basic referee schedule exporter
 */
class ScheduleGameUtilDumpCSV
{
    public function getFileExtension() { return 'csv'; }
    public function getContentType()   { return 'text/csv'; }
    
    public function dump($games,$dumpOfficials = false)
    {
        $fp = fopen('php://temp','r+');

        // Header
        $row = array(
            "Game","Date","DOW","Time","Venue","Field",
            "Group","HT Slot","AT Slot",
            "Home Team Name",'Away Team Name',
        );
        if ($dumpOfficials)
        {
            $row = array_merge($row,array('Referee','AR1','AR2'));
        }
        fputcsv($fp,$row);

        // Games is passed in
        foreach($games as $game)
        {
            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            $date = $dt->format('m/d/Y');
            $time = $dt->format('g:i A');
            
            // Build up row
            $row = array();
            $row[] = $game->getNum();
            $row[] = $date;
            $row[] = $dow;
            $row[] = $time;
            $row[] = $game->getVenueName();
            $row[] = $game->getFieldName();
    
            $row[] = $game->getGroupKey();
            
            $row[] = $game->getHomeTeam()->getGroupSlot();
            $row[] = $game->getAwayTeam()->getGroupSlot();
            $row[] = $game->getHomeTeam()->getName();
            $row[] = $game->getAwayTeam()->getName();
    
            if ($dumpOfficials)
            {
                $officials = $game->getOfficials();
                foreach($officials as $official)
                {
                    $row[] = $official->getPersonNameFull();
                }
            }
            fputcsv($fp,$row);
        }
        // Return the content
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }
}
?>
