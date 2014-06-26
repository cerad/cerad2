<?php

namespace Cerad\Bundle\PersonBundle\Action\PersonFed\Reader;

use Cerad\Bundle\CoreBundle\Excel\ExcelReader;

class PersonFedReaderKaren extends ExcelReader
{   
    protected $record = array
    (
        'aysoId'  => array('cols' => 'AYSO ID','req' => true),
        
        'checked' => array('cols' => 'Checked?',   'req' => true),
        
        'name'  => array('cols' => 'Name', 'req' => true),
        'email' => array('cols' => 'Email','req' => true),
    );
    protected function processItem($item)
    {
        $aysoId = $item['aysoId']; // 8 digits
        if (!$aysoId) return;
        
        $checked = $item['checked'];
        switch ($checked)
        {
            case 'Cleared':
            case 'Youth':
                break;
            default:
                return;
        }
        
        $this->items[] = array(
            'fedKey'    => 'AYSOV' . $aysoId,
            'name'      => $item['name'],
            'email'     => $item['email'],
            'memYear'   => 'MY2013',
            'safeHaven' => 'AYSO',
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
