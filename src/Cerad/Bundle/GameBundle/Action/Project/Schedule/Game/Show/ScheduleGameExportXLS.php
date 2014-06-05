<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Schedule\Game\Show;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;

class ScheduleGameExportXLS extends ExcelExport
{
    protected $gameTransformer;
    
    public function __construct($gameTransformer)
    {
        $this->gameTransformer = $gameTransformer;
    }
    protected $counts = array();
    
    protected $widths = array
    (
        'Game' =>  6, 'Game#' =>  6,

        'DOW' =>  5, 'Date' =>  12, 'Time' => 10,
        
        'Venue' => 16, 'Field' =>  6, 'Type' => 5, 'Group' => 22, 'Level' => 12,
        'Division' => 16,
        
        'Home Team Slot' => 12, 'Away Team Slot' => 12,
        
        'HT Slot' => 10, 'AT Slot' => 10,
        
        'Home Team Group' => 26, 'Away Team Group' => 26,
        'Home Team Name'  => 26, 'Away Team Name'  => 26,
        
        'Referee' => 26, 'AR1' => 26, 'AR2' => 26,
        
        'Name' => 26, 'Pos' => 6, 'YC' => 3, 'RC' => 3,
    );
    protected $center = array
    (
        'Game',
    );
    
    protected function setHeaders($ws,$map,$row = 1)
    {
        $col = 0;
        foreach(array_keys($map) as $header)
        {
            $ws->getColumnDimensionByColumn($col)->setWidth($this->widths[$header]);
            $ws->setCellValueByColumnAndRow($col++,$row,$header);
            
            if (in_array($header,$this->center) == true)
            {
                // Works but not for multiple sheets?
                // $ws->getStyle($col)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
        return $row;
    }
    protected function setRow($ws,$map,$person,&$row)
    {
        $row++;
        $col = 0;
        foreach($map as $propName)
        {
            $ws->setCellValueByColumnAndRow($col++,$row,$person[$propName]);
        }
        return $row;
    }
    /* ========================================================
     * Generates the games listing
     */
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
          //'Level'    => 'level',
          //'Group'    => 'group',
          //'Type'     => 'type',
            'Group'    => 'groupKey',
            'HT Slot'  => 'homeTeamGroupSlot',
            'AT Slot'  => 'awayTeamGroupSlot',
            'Home Team Name'  => 'homeTeamName',
            'Away Team Name'  => 'awayTeamName',
        );
        $ws->setTitle('Games');
        
        $row = $this->setHeaders($ws,$map);
        
        $timex = null;
        
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
          //$date = $dt->format('n/j/Y'); // 'M d y'
          //$time = $dt->format('g:i A'); // 'H:i A'
            
            $time = $dt->format('G:i');   // 13:45
            $date = $dt->format('Y-m-d'); // yyyy-mm-dd
            
            // Skip on time changes
            if ($timex != $time)
            {
                if ($timex) $row++;
                $timex = $time;
            }
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getNum());
            $ws->setCellValueByColumnAndRow($col++,$row,$date);
            $ws->setCellValueByColumnAndRow($col++,$row,$dow);
            $ws->setCellValueByColumnAndRow($col++,$row,$time);
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getVenueName());
            $ws->setCellValueByColumnAndRow($col++,$row,$game->getFieldName());
            
          //$ws->setCellValueByColumnAndRow($col++,$row,$game->getLevelKey());
            $ws->setCellValueByColumnAndRow($col++,$row,substr($game->getGroupKey(),0));
          //$ws->setCellValueByColumnAndRow($col++,$row,$game->getGroupType());
          //$ws->setCellValueByColumnAndRow($col++,$row,$this->gameTransformer->gameLevel($game));
            
          //$ws->setCellValueByColumnAndRow($col++,$row,$this->gameTransformer->gameTeamGroup($homeTeam));
          //$ws->setCellValueByColumnAndRow($col++,$row,$this->gameTransformer->gameTeamGroup($awayTeam));
            $ws->setCellValueByColumnAndRow($col++,$row,$homeTeam->getGroupSlot());
            $ws->setCellValueByColumnAndRow($col++,$row,$awayTeam->getGroupSlot());
            
            $ws->setCellValueByColumnAndRow($col++,$row,$homeTeam->getName());
            $ws->setCellValueByColumnAndRow($col++,$row,$awayTeam->getName());
        }
        return;
    }
    /* =======================================================================
     * Main entry point
     */
    public function generate($games)
    {
        // Spreadsheet
        $ss = $this->createSpreadsheet(true); 
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
        $objWriter = $this->createWriter($ss);

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
    public function getFileExtension() { return 'xlsx'; }
    public function getContentType()   { return 'application/vnd.ms-excel'; }
}
?>
