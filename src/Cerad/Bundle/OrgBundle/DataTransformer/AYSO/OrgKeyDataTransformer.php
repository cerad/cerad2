<?php
namespace Cerad\Bundle\OrgBundle\DataTransformer\AYSO;

use Symfony\Component\Form\DataTransformerInterface;

class OrgKeyDataTransformer implements DataTransformerInterface
{           
    protected $orgRepo;
    
    public function __construct($orgRepo)
    {
        $this->orgRepo = $orgRepo;
    }
    public function transform($orgKey)
    {
        if (!$orgKey) return '';
        
        $org = $this->orgRepo->find($orgKey);

        if (!$org) return substr($orgKey,4);

        $orgParentKey = $org->getParent();
        
        $section = (int) substr($orgParentKey,5,2);
        $area    =       substr($orgParentKey,7,1);
        $region  = (int) substr($orgKey,5);
        
        return sprintf('%02u-%s-%04u',$section,$area,$region);
    }
    public function reverseTransform($sar)
    {
    
    }
    public function transformToParts($orgKey)
    {
        $sar = $this->transform($orgKey);
        if (!$sar) return null;
        
        $sarParts = explode('-',$sar);
        if (count($sarParts) != 3) return null;
        
        return array(
            'section' => (int) $sarParts[0],
            'area'    =>       $sarParts[1],
            'region'  => (int) $sarParts[2],
        );
    }
}
?>
