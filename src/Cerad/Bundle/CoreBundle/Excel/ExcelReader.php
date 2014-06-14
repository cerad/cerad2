<?php
namespace Cerad\Bundle\CoreBundle\Excel;

/* ========================================================
 * 23 Sep 2013
 * This is intened to be extended by import routines
 * 
 * Might be metter to just use $excel directly
 * 
 * 03 June 2014
 * This is always consfusing whenever I look at it
 * Excel should be reader
 * Do I really need Loader and Reader?
 * 
 * 12 June 2014
 * Copied from loader 
 * Wanted to deal better with optional columns such as Referee
 * 
 * 14 June 2014
 * Getting rid of the reader dependency
 */
class ExcelReader
{
    protected $items  = array();
    protected $errors = array();
    
    protected $record = array(
        'region' => array('cols' => 'Region', 'req' => true,  'default' => 0, 'plus' => true),
    );
    protected $map = array(); // Column index for column name
    
    /* ==========================================================
     * Used to actually read the file
     * Excel 2007
     * Excel 5(2003)
     * CSV
     */
    protected function createLoaderForFile($fileName,$readDataOnly = true)
    {
        // Excel 2007
        if (class_exists('ZipArchive')) 
        {
            $reader2 = new \PHPExcel_Reader_Excel2007();
        
            $reader2->setReadDataOnly($readDataOnly);
        
            if ($reader2->canRead($fileName)) return $reader2;
        }
        // Excel 2003
        $reader1 = new \PHPExcel_Reader_Excel5();
        
        $reader1->setReadDataOnly($readDataOnly);
        
        if ($reader1->canRead($fileName)) return $reader1;
        
        // Note that csv does not actually check for a csv file
        $reader3 = new \PHPExcel_Reader_CSV();
        
        if ($reader3->canRead($fileName)) return $reader3;
        
        throw new \Exception("No Reader found for $fileName");
    }
    
    /* ==================================================
     * Converts row to item
     */
    protected function processDataRow($row)
    {
        $item = array();
        foreach($this->record as $name => $params)
        {
            if (isset($params['default'])) $default = $params['default'];
            else                           $default = null;
            $item[$name] = $default; // Even if not found, item will get an entry
        }
        foreach($row as $index => $value)
        {
            if (isset($this->map[$index]))
            {
                $name = $this->map[$index];
                $item[$name] = trim($value); // If found always get something not null
            }
        }
        return $item;
    }
    /* ====================================================
     * Typically the first row on the sheet
     * Generates $map
     */
    protected function processHeaderRow($row)
    {
        $found  = array();
        $record = $this->record;
        foreach($row as $index => $colName)
        {
            $colName = trim($colName);
            foreach($record as $name => $params)
            {
                if (is_array($params['cols'])) $cols = $params['cols'];
                else                           $cols = array($params['cols']);
                foreach($cols as $col)
                {
                    if ($col == $colName)
                    {
                        if (isset($params['plus'])) $plus = $params['plus'];
                        else                        $plus = 0;
                        
                        $this->map[$index + $plus] = $name;
                        $found[$name] = true;
                    }
                }
            }
        }

        // Make sure all required attributes found
        foreach($record as $name => $params)
        {
            if (isset($params['req']) && $params['req'])
            {
                if (!isset($found[$name]))
                {
                    if (is_array($params['cols'])) $cols = $params['cols'];
                    else                           $cols = array($params['cols']);
                    $cols = implode(' OR ',$cols);
                    $this->errors[] = "Missing $cols";
                }
            }
        }
    }
    /* =========================================================
     * Main entry point
     * Loads worksheet and calla processItem for each row
     */
    public function load($fileName, $worksheetName = null)
    {
        $loader = $this->createLoaderForFile($fileName);
        
        $ss = $loader->load($fileName);

        if ($worksheetName) $ws = $ss->getSheetByName($worksheetName);
        else                $ws = $ss->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        // Insert each record
        foreach($rows as $row)
        {
            $item = $this->processDataRow($row);
            
            $this->processItem($item);
        }
        return $this->items;
    }
    protected function processItem($item)
    {
        print_r($item); die("\n");
    }
    /* ==================================================
     * 15 Oct 2013
     * Tested mostly on xlsx
     */
    protected function processTime($time)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($time,'hh:mm:ss');
    }
    protected function processDate($date)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($date,'yyyy-MM-dd');
    }
    protected function processDateTime($dateTime)
    {
        return \PHPExcel_Style_NumberFormat::toFormattedString($dateTime,'yyyy-MM-dd hh:mm:ss');
    }
}
?>
