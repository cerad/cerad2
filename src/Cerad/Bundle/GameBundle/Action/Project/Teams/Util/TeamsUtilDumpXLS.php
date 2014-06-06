<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Util;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;

class TeamsUtilDumpXLS extends ExcelExport
{
    protected function setHeaders($ws,$map,$row = 1)
    {
        $col = 0;
        
        foreach($map as $item)
        {
            $width = isset($item['width']) ? $item['width'] : 12;
            $ws->getColumnDimensionByColumn($col)->setWidth($width);
            
            $center = isset($item['center']) ? $item['center'] : false;
            if ($center)
            {
                $colx = chr(ord('A') + $col);
                $colAlign = $ws->getStyle($colx)->getAlignment();
                $colAlign->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
            }
            $ws->setCellValueByColumnAndRow($col,$row,$item['hdr']);
            
            $col++;
        }
        return $row++;
    }
    /* =======================================================================
     * Process a team
     */
    protected function processTeam($ws,$model,$program,$team,&$row)
    {
        $region = $team->getOrgKey();
        if (!$region && false)
        {
            $region = $team->getName();
            if (strpos($region,'Team ') === 0) $region = null;
        }
        $col = 0;
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getLevelKey());
        $ws->setCellValueByColumnAndRow($col++,$row,$team->getNum());
        $ws->setCellValueByColumnAndRow($col++,$row,$region);
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
     * Process each program
     */
    protected function processProgram($ss,&$sheetNum,$model,$program)
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
        
        $ws = $ss->createSheet($sheetNum++);
        
        $pageSetup = $ws->getPageSetup();
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $pageSetup->setFitToPage  (true);
        $pageSetup->setFitToWidth (1);
        $pageSetup->setFitToHeight(0);
        $ws->setPrintGridLines    (true);
        
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
            $this->processTeam($ws,$model,$program,$team,$row);
        }        
    }
    /* =======================================================================
     * Main entry point
     */
    public function generate($model)
    {
        // Spreadsheet
        $ss = $this->createSpreadsheet(); 
        $sheetNum = 0;
        
        $programs = $model->getPrograms();
        foreach($programs as $program)
        {
            $this->processProgram($ss,$sheetNum,$model,$program);
        }
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
