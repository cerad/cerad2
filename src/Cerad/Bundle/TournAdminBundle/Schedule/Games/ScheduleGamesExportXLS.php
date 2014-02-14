<?php

namespace Cerad\Bundle\TournAdminBundle\Schedule\Games;

use Cerad\Component\Excel\Export as BaseExport;

/* ============================================
 * Basic referee schedule exporter
 */
class ScheduleGamesExportXLS extends BaseExport
{
    protected $columnWidths = array
    (
        'Game' =>  6, 'Game#' =>  6,

        'DOW' =>  5, 'Date' =>  12, 'Start' => 10, 'Stop' => 10,

        'Venue' =>  8, 'Field' =>  10, 
        
        'Level' => 16, 'Group' => 10, 'GT' => 4,

        'HT Group'  => 12, 'AT Group'  => 12,
        'Home Team' => 12, 'Away Team' => 12,
    );
    protected $columnCenters = array
    (
        'Game',
    );
    /* =========================================================
     * The game sheet
     */
    public function generateGames($ws,$games)
    {
        // Only the keys are currently being used
        $headers = array(
            'Game',
            'Date',  'DOW', 'Start', 'Stop',
            'Venue', 'Field',
            'Level', 'Group', 'GT',
            'HT Group', 'Home Team', 'Away Team', 'AT Group',
            'Game#',
        );
        $ws->setTitle('Games');

        $row = $this->setHeaders($ws,$headers);
        
        $startx = null;

        foreach($games as $game)
        {            
            $row++;
            $col = 0;

            // Date/Time
            $dt    = $game->getDtBeg();
            $dow   = $dt->format('D');
            $date  = $this->getNumericDate($dt);
            $start = $this->getNumericTime($dt);
            $stop  = $this->getNumericTime($game->getDtEnd());

            // Skip on time changes
            if ($startx != $start)
            {
                if ($startx) $row++;
                $startx = $start;
            }
            $this->setCellValueByColumnAndRow($ws,$col++,$row,$game->getNum());
            $this->setCellValueByColumnAndRow($ws,$col++,$row,$date,'mm/dd/yyyy');
            $this->setCellValueByColumnAndRow($ws,$col++,$row,$dow);
            $this->setCellValueByColumnAndRow($ws,$col++,$row,$start,'h:mm AM/PM');
            $this->setCellValueByColumnAndRow($ws,$col++,$row,$stop, 'h:mm AM/PM');
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getVenue());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getName ());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getLevelId());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getGroup());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getGroupType());

            $ws->setCellValueByColumnAndRow($col++,$row,$game->getHomeTeam()->getGroup());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getHomeTeam()->getName ());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getAwayTeam()->getName ());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getAwayTeam()->getGroup());

            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
        }
        return;
    }
    /* ======================================================================
     * Main entry point
     */
    protected $project;
    
    public function generate($project,$games)
    {
        // Project specific customization
        $this->project = $project;
        
        // Spreadsheet
        $this->ss = $ss = $this->createSpreadSheet();
        $ws = $ss->getSheet(0);

        $ws->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $ws->getPageSetup()->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $ws->getPageSetup()->setFitToPage(true);
        $ws->getPageSetup()->setFitToWidth(1);
        $ws->getPageSetup()->setFitToHeight(0);
        $ws->setPrintGridLines(true);

        $this->generateGames($ws,$games);

        // Output
        $ss->setActiveSheetIndex(0);
        
        return;
    }
 }
?>
