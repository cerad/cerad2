<?php
namespace Cerad\Bundle\LevelBundle\InMemory;

use Symfony\Component\Yaml\Yaml;

use Cerad\Bundle\LevelBundle\Model\Level;
use Cerad\Bundle\LevelBundle\Model\LevelRepositoryInterface;

class LevelRepository implements LevelRepositoryInterface
{
    protected $levels = array();
    
    public function __construct($files)
    {
        foreach($files as $file)
        {
            $configs = Yaml::parse(file_get_contents($file));
            
            foreach($configs as $id => $config)
            {
                $config['id'] = $id;
                $level = new Level($config);
                $this->levels[$id] = $level;
            }
        }
    }
    public function find($id)
    {
        return isset($this->levels[$id]) ? $this->levels[$id] : null;
    }
    public function findAll()
    {
        return $this->levels;        
    }
    /* ==========================================================
     * Simulating a query
     */
    public function queryKeys($params)
    {
        // Hack to make VIP gender nuetral
        if (isset($params['ages']) && is_array($params['ages']))
        {
            if (in_array('VIP',$params['ages']))
            {
                $params['genders'][] = 'VIP';
            }
        }
        $keys = array();
        foreach($this->levels AS $level)
        {
            if ($this->filter($params,$level)) $keys[] = $level->getKey();
        }
        // If everything was picked
        if (count($keys) == count($this->levels)) return array();
        
        return $keys;
    }
    protected function filter($params,$level)
    {
        if (!$this->filterProperty($params,'programs',$level->getProgram())) return false;
        if (!$this->filterProperty($params,'genders' ,$level->getGender ())) return false;
        if (!$this->filterProperty($params,'ages',    $level->getAge    ())) return false;
        
        // Might want to handle divs as well U10G
        
        return true;
        
    }
    protected function filterProperty($params,$name,$value)
    {
      //print_r($params); 
      //echo sprintf("<br />   %s %s\n",$name,$value); die();
        $value = strtolower($value);
        
        if (!isset($params[$name])) return true;
        
        $props = $params[$name];
        if (!is_array($props)) $props = array($props);

        if (count($props) < 1) return true;
        
        foreach($props as $prop)
        {
            $parts = explode(',',$prop);
            foreach($parts as $part)
            {
                // Case insensitive like mysql
                if (strtolower(trim($part)) == $value) return true;
            }
        }
        return false;
    }
    /* ============================================================
     * 20 June 2014
     * Rewrote to use arguments instead of criteria array
     */
    public function findAllByPGA($project,$programs,$genders,$ages)
    {
        // Ignore project
        if ($project);
        
        // Need this but shouldn't
        //$programs = is_array($programs) ? $programs : array();
        //$genders  = is_array($genders)  ? $genders  : array();
        //$ages     = is_array($ages)     ? $ages     : array();
        
        // Hack for VIP genders
        if (in_array('VIP',$ages)) {
            if (!in_array('VIP',$genders)) $genders[] = 'VIP';
        }
        $levels = array();
        foreach($this->levels as $level)
        {
            if ($this->keep($level,$programs,$genders,$ages)) $levels[] = $level;
        }
        return count($levels) != count($this->levels) ? $levels : array();
    }
    protected function keep($level,$programs,$genders,$ages)
    {
        if (!$this->keepProperty($level->getProgram(),$programs)) return false;
        if (!$this->keepProperty($level->getGender (),$genders))  return false;
        if (!$this->keepProperty($level->getAge    (),$ages))     return false;
        return true;
    }
    protected function keepProperty($value,$props)
    {
        if (!is_array($props) || count($props) < 1) return true;
        
        $value = strtolower($value);
        foreach($props as $prop)
        {
            if (strtolower(trim($prop)) == $value) return true;
        }
        return false;
    }
}

?>
