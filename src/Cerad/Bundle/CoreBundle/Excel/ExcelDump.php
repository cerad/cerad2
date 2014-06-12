<?php

/* ========================================================================
 * 07 June 2014
 * 
 * Copied the Export file and made some lessons learned changes
 * 
 * Base class for dumping excel spreadsheets to a file or response
 * 
 * Assume for now that Excell2007 is being written
 * 
 * Not all of the formatting stuff works properly under Excel5 aka 2003
 */
namespace Cerad\Bundle\CoreBundle\Excel;

class ExcelDump
{
    protected function createSpreadSheet($myValueBinder = true)
    {
        if ($myValueBinder) \PHPExcel_Cell::setValueBinder( new ExcelValueBinder() );
        
        // TODO: Wrap this 
        return new \PHPExcel();
    }
    protected function createWriter($ss)
    {
        return \PHPExcel_IOFactory::createWriter($ss, 'Excel2007');
    }
    protected function createWorkSheet($ss,$num,$land = true)
    {
        $ws = $ss->createSheet($num);
        
        if ($land)
        {
            $this->setWorkSheetFormatLandscape($ws);
        }
        return $ws;
    }
    protected function setWorkSheetFormatLandscape($ws)
    {
        $pageSetup = $ws->getPageSetup();
        $pageSetup->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize  (\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $pageSetup->setFitToPage  (true);
        $pageSetup->setFitToWidth (1);
        $pageSetup->setFitToHeight(0);
        $ws->setPrintGridLines    (true);
    }
    /* =====================================================
     * $map array('hdr' => 'Team','key' => 'num','width' => 6, 'center' => true),
     */
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
                // A or B or C etc
                $coord = \PHPExcel_Cell::stringFromColumnIndex($col);
                
                $colAlign = $ws->getStyle($coord)->getAlignment();
                $colAlign->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
            }
            $ws->setCellValueByColumnAndRow($col,$row,$item['hdr']);
            
            $col++;
        }
        return $row++;
    }
    /* ======================================================================
     * Set a cell value with an optional format
     */
    protected function setCellValueByColumnAndRow($ws,$col,$row,$value,$format = null)
    {
        $ws->setCellValueByColumnAndRow($col,$row,$value);
        
        if (!$format) return;
        
        die('TODO: ExcelDump::setCellFormat');
 
        $coord = \PHPExcel_Cell::stringFromColumnIndex($col) . $row;
        
        $ws->getStyle($coord)->getNumberFormat()->setFormatCode($format);
    }    
    protected function setRow($ws,$map,$item,&$row)
    {
        die('TODO: Refactor ExcelDump::setRow');
        $row++;
        $col = 0;
        foreach($map as $propName)
        {
            $ws->setCellValueByColumnAndRow($col++,$row,$item[$propName]);
        }
        return $row;
    }
 
    /* =======================================================
     * Called by controller to get the content
     * Or just returned from the dumper
     */
    protected $ss;

    public function getBuffer($ss = null)
    {
        if (!$ss) $ss = $this->ss;
        if (!$ss) return null;

        $objWriter = $this->createWriter($ss); // \PHPExcel_IOFactory::createWriter($ss, 'Excel5');

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        return ob_get_clean();
    }
    public function getFileExtension() { return 'xlsx'; }
    public function getContentType()   { return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; }
    
    /* ========================================================
     * Returns the excel numeric value for a given time
     * Keep for now but these are not used
     */
    protected function getNumericTime($dt)
    {
        $hours   = $dt->format('H');
        $minutes = $dt->format('i');
        
       return ($hours / 24) + ($minutes / 1440);
    }
    protected function getNumericDate($dt)
    {
        $date = $dt->format('Y-m-d');
        
        return \PHPExcel_Shared_Date::stringToExcel($date);
    }
    
}
?>
