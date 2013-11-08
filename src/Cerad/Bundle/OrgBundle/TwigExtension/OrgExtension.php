<?php
namespace Cerad\Bundle\OrgBundle\TwigExtension;

class OrgExtension extends \Twig_Extension
{
    protected $env;
    protected $orgRepo;
    
    public function getName()
    {
        return 'cerad_org_extension';
    }
    public function __construct($orgRepo)
    {
        $this->orgRepo = $orgRepo;        
    }
    public function initRuntime(\Twig_Environment $env)
    {
        parent::initRuntime($env);
        $this->env = $env;
    }
    protected function escape($string)
    {
        return twig_escape_filter($this->env,$string);
    }
    public function getFilters()
    {
        return array(            
            'cerad_org_sar' => new \Twig_Filter_Method($this, 'sar'),   
        );
    }
    // Return section area region
    public function sar($orgId)
    {
        $org = $this->orgRepo->find($orgId);
        
        if (!$org) return substr($orgId,4);
        
        return substr($org->getParent(),4) . '-' . substr($orgId,4);
        
    }

}
?>
