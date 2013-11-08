<?php
namespace Cerad\Bundle\OrgBundle\Services\Orgs;

use Cerad\Component\Excel\Import as BaseImport;

class OrgsImportResults
{
    
}
class OrgsImportXLS extends BaseImport
{   
    protected $orgRepo;
    
    protected $record = array
    (
        'section' => array('cols' => 'SectionName', 'req' => true),
        'area'    => array('cols' => 'AreaName',    'req' => true),
        'region'  => array('cols' => 'RegionNumber','req' => true),
    );
    public function __construct($orgRepo)
    {
        parent::__construct();
        
        $this->orgRepo = $orgRepo;
    }
    /* ===============================================
     * Processes each item
     */
    protected function processItem($item)
    {
        $region = (int)$item['region'];
        if (!$region) return;
        
        $area = $item['area'];
        
        $section = (int)$item['section'];
        
        $this->results->totalOrgCount++;
        
        $orgId  = sprintf('AYSOR%04u',  $region);
        $parent = sprintf('AYSOA%02u%s',$section,$area);
        
        $org = $this->orgRepo->find($orgId);
        if (!$org)
        {
            $org = $this->orgRepo->createOrg();
            $org->setId($orgId);
        }
        $org->setParent($parent);
        $this->orgRepo->save($org);
        
        return;        
    }
    /* ==============================================================
     * Almost like the load but with a few tweaks
     * Main entry point
     * Returns a Results object
     */
    public function import($params)
    {
        $ss = $this->reader->load($params['filepath']);

      //if ($worksheetName) $ws = $reader->getSheetByName($worksheetName);
        $ws = $ss->getSheet(0);
        
        $rows = $ws->toArray();
        
        $header = array_shift($rows);
        
        $this->processHeaderRow($header);
        
        $this->results = new OrgsImportResults();
        $this->results->basename = $params['basename'];
        $this->results->totalOrgCount    = 0;
        $this->results->modifiedOrgCount = 0;
        
        // Insert each record
        foreach($rows as $row)
        {
            $item = $this->processDataRow($row);
            
            $this->processItem($item);
        }
        $this->orgRepo->commit();
        
        return $this->results;
    }
}
?>
