<?php

namespace Cerad\Bundle\GameBundle\Action\Project\Teams\Export;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;

class TeamsExportXLS extends ExcelExport
{
    protected $widths = array
    (
        'Level'  => 20,
        'Team'   =>  6,
        'Region' => 12,
        'Name'   => 20,
        'Pts'    =>  4,        
        'Slots'  => 24,        
    );
    protected function setHeaders($ws,$map,$row = 1)
    {
        $col = 0;
        foreach(array_keys($map) as $header)
        {
            $ws->getColumnDimensionByColumn($col)->setWidth($this->widths[$header]);
            $ws->setCellValueByColumnAndRow($col++,$row,$header);
        }
        return $row;
    }
    /* =======================================================================
     * Process a team
     */
    protected function processTeam($ws,$model,$program,$team,&$row)
    {
        $region = $team->getOrgKey();
        if (!$region)
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
        $row++;
    }
     /* =======================================================================
     * Process each program
     */
    protected function processProgram($ss,&$sheetNum,$model,$program)
    {
        $map = array(
            'Level'  => 'levelKey',
            'Team'   => 'num',
            'Region' => 'orgKey',
            'Name'   => 'name',
            'Pts'    => 'points', 
            
            'Slots'  => null,        
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
