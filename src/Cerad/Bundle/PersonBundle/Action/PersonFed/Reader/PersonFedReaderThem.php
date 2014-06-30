<?php

namespace Cerad\Bundle\PersonBundle\Action\PersonFed\Reader;

use Cerad\Bundle\CoreBundle\Excel\ExcelReader;

/* =========================================
 * 29 June 2014
 * Got a file via Rick from "them".  
 */
class PersonFedReaderThem extends ExcelReader
{   
    // Green Excel Checkmark - did not work with strpos
    const CHECKED = 'ΓêÜ';
    
    protected $record = array
    (
        'aysoId'  => array('cols' => 'AYSO ID','req' => true),
        
      //'checked' => array('cols' => 'Checked?',   'req' => true),
        
        'name'   => array('cols' => 'Name', 'req' => true),
        'email'  => array('cols' => 'Email','req' => true),
        'phone'  => array('cols' => 'Cell Phone','req' => true),
        
        'region'    => array('cols' => 'Region','req' => true),
        'safeHaven' => array('cols' => 'Safe Haven certified','req' => true),
        'volForm'   => array('cols' => 'E-signed','req' => true),
    );
    protected function processItem($item)
    {
        $aysoId = $item['aysoId']; // 8 digits
        if (!$aysoId) return;
        
        $memYear   = strpos($item['volForm'],  'MY2013') !== false ? 'MY2013' : null;
        $safeHaven = strpos($item['safeHaven'],'AYSO')   !== false ? 'AYSO'   : null;
        
        $region = (int)substr($item['region'],1);
        
        if (($region < 1) || ($region > 9999)) $region = null;
        
      //echo sprintf("%-20s '%-6s' '%-4s' %04u\n",$item['name'],$memYear,$safeHaven,$region);
        
        $this->items[] = array(
            'fedKey'    => 'AYSOV' . $aysoId,
            'name'      => $item['name'],
            'email'     => $item['email'],
            'phone'     => $item['phone'],
            'region'    => $region,
            'memYear'   => $memYear,
            'safeHaven' => $safeHaven,
        );
    }
    /* ==============================================================
     * Almost like the load but with a few tewaks
     */
    public function read($filePath,$workSheetName = null)
    {   
        return $this->load($filePath,$workSheetName);
    }
}
?>
