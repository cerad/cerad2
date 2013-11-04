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

        'DOW' =>  5, 'Date' =>  12, 'Time' => 10,

        'Venue' =>  8, 'Field' =>  6, 'Type' => 5, 'Pool' => 12,

        'Home Team' => 26, 'Away Team' => 26,

    );
    protected $columnCenters = array
    (
        'Game',
    );
    public function generateGames($ws,$games)
    {
        // Only the keys are currently being used
        $map = array(
            'Game'     => 'game',
            'Date'     => 'date',
            'DOW'      => 'dow',
            'Time'     => 'time',
            'Venue'    => 'venue',
            'Field'    => 'field',
            'Type'     => 'type',
            'Pool'     => 'pool',

            'Home Team' => 'homeTeam',
            'Away Team' => 'awayTeam',

            'Game#'   => 'game',
        );
        $ws->setTitle('Games');

        $row = $this->setHeaders($ws,$map);
        
        $timex = null;

        foreach($games as $game)
        {
            $num = $game->getNum();
            $skip = false;
            if ($num > 10 && $num < 20) $skip = true;
            switch($num)
            {
                case 34: 
                case 55:
                    break;
                default: ; //$skip = true;
            }
            if ($skip) continue;
            
            $row++;
            $col = 0;

            // Date/Time
            $dt   = $game->getDtBeg();
            $dow  = $dt->format('D');
            
            $date = $dt->format('M d y');
          //$time = '_' . $dt->format('H:i A'); //('g:i A');
          //$time = $dt->format('H:i');
            $time = $this->getExcelTime($dt);

            // Skip on time changes
            /*
            if ($timex != $time)
            {
                if ($timex) $row++;
                $timex = $time;
            }*/
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
            $ws->setCellValueByColumnAndRow($col++,$row,$date);
            $ws->setCellValueByColumnAndRow($col++,$row,$dow);
            
          //$ws->setCellValueByColumnAndRow($col++,$row,$time);
            $this->setCellValueByColumnAndRow($ws,$col++,$row,$time,'h:mm AM/PM');
            
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getVenue());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getField()->getName ());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getGroup());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getLevelId());

            $ws->setCellValueByColumnAndRow($col++,$row,$game->getHomeTeam()->getName());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getAwayTeam()->getName());

            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
        }
        return;
    }
    protected $project;
    
    public function generate($project,$games)
    {
        // Project specific customization
        $this->project = $project;
        
        die('generate');
        
        // Spreadsheet
        $ss = $this->excel->newSpreadSheet();
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
        
      //$objWriter = $this->excel->newWriter($ss); // \PHPExcel_IOFactory::createWriter($ss, 'Excel5');

        $objWriter = \PHPExcel_IOFactory::createWriter($ss, 'Excel2007');

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
    /* ========================================================
     * Returns the excel numeric value for a given time
     */
    protected function getExcelTime($dt)
    {
        $hours   = $dt->format('H');
        $minutes = $dt->format('i');
        
       return ($hours / 24) + ($minutes / 1440);

    }
    protected function setCellValueByColumnAndRow($ws,$col,$row,$value,$format = null)
    {
        $ws->setCellValueByColumnAndRow($col,$row,$value);
        if (!$format) return;

        $coord = \PHPExcel_Cell::stringFromColumnIndex($col) . $row;
        
        $ws->getStyle($coord)->getNumberFormat()->setFormatCode($format);
        
    }

}
?>
