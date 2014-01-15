<?php
namespace Cerad\Bundle\TournBundle\TwigExtension;

use Cerad\Bundle\PersonBundle\DataTransformer\PhoneTransformer;

class TournExtension extends \Twig_Extension
{
    protected $env;
    protected $project;
    protected $orgRepo;
    protected $showConfig;
    
    public function getName()
    {
        return 'cerad_tourn_extension';
    }
    public function __construct($project,$showConfigs,$orgRepo)
    {
        $this->project = $project;
        $this->orgRepo = $orgRepo;
        
        $configName = defined('CERAD_TOURN_SHOW_CONFIG') ? CERAD_TOURN_SHOW_CONFIG : 'default';

        if (!isset($showConfigs[$configName]))
        {
            throw new \Exception('Undefined show config : ' . $configName);
        }
        $this->showConfig = $showConfigs[$configName];
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
            'cerad_phone'   => new \Twig_Filter_Method($this, 'phone'),   
            'cerad_org_sar' => new \Twig_Filter_Method($this, 'sar'),
        );
    }
    public function getFunctions()
    {
        return array(            
            'cerad_tourn_show' => new \Twig_Function_Method($this, 'show'),
            
            'cerad_tourn_is_local'    => new \Twig_Function_Method($this, 'isLocal'),
            'cerad_tourn_get_referer' => new \Twig_Function_Method($this, 'getReferer'),
            
            'cerad_tourn_get_project_title'       => new \Twig_Function_Method($this, 'getProjectTitle'),
            'cerad_tourn_get_project_description' => new \Twig_Function_Method($this, 'getProjectDescription'),            
        );
    }
    public function getProjectDescription()
    {
        return $this->project->getDesc();
    }
    public function getProjectTitle()
    {
        return $this->project->getTitle();
    }
    public function getReferer()
    {
        // Should be a better way than to access $_SERVER directly.
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if (!$url) return null;
        
        $parts = parse_url($url);
        
        $referer = sprintf('%s://%s/',$parts['scheme'],$parts['host']);
      //die($referer);
        return $referer;
        
    }
    /* =======================================================
     * 15 Jan 2014 - Copied from s1games
     * Looks identical to getReferer
     * TODO: is it still being used?
     */
    public function isLocal()
    {
        // Should be a better way than to access $_SERVER directly.
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        if (!$url) return null;

        $parts = parse_url($url);

        $islocal = sprintf('%s://%s/',$parts['scheme'],$parts['host']);

        return $islocal;
    }    
    public function show($param)
    {
        if (!isset($this->showConfig[$param]))
        {
            throw new \Exception('Undefined show config param : ' . $param);
        }
        return $this->showConfig[$param];
    }
    
    /* ===================================================
     * Phone transformer
     */
    protected $phoneTransformer;
    
    public function phone($value)
    {
        if (!$this->phoneTransformer) $this->phoneTransformer = new PhoneTransformer();
        
        return $this->phoneTransformer->transform($value);
    }
    // Return section area region
    public function sar($orgId)
    {
        $org = $this->orgRepo->find($orgId);

        if (!$org) return substr($orgId,4);

        return (int) substr($org->getParent(),5,2) . '/' . substr($org->getParent(),7,1) . '/' . (int) substr($orgId,5);
    }
}
?>
