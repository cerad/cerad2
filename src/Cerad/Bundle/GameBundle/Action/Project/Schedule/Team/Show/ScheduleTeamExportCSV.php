<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Team\Show;

/* ============================================
 * Basic referee schedule exporter
 */
class ScheduleTeamExportCSV
{
    public function getFileExtension() { return 'csv'; }
    public function getContentType()   { return 'text/csv'; }
    public function generate($games)
    {
        $fp = fopen('php://temp','r+');

        // Header
        $row = array(
            "Game","Date","DOW","Time","Field",
            "Type","Home Team Group","Home Team","Away Team",'Away Team Group',
        );
        fputcsv($fp,$row);

        // Games is passed in
        foreach($games as $game)
        {
            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            $date = $dt->format('M d');
            $time = $dt->format('g:i A');
            
            // Build up row
            $row = array();
            $row[] = $game->getNum();
            $row[] = $date;
            $row[] = $dow;
            $row[] = $time;
            $row[] = $game->getFieldName();
    
            $row[] = $game->getGroupType();
            $row[] = $game->getHomeTeam()->getGroupSlot();
            $row[] = $game->getHomeTeam()->getName();
            $row[] = $game->getAwayTeam()->getName();
            $row[] = $game->getAwayTeam()->getGroupSlot();
    
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
