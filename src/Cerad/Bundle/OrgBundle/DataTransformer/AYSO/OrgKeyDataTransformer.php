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
    public function reverseTransform($value)
    {
        die('OrgKeyDataTransformer::reverseTransform ' . $value);
        
        $id = (int)preg_replace('/\D/','',$value);
        
        if (!$id) return null;
        
        return sprintf('AYSOR%04u',$id);
    }
}
?>
