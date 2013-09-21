<?php
namespace Cerad\Bundle\LevelBundle\InMemory;

use Symfony\Component\Yaml\Yaml;

use Cerad\Bundle\LevelBundle\Model\Level;
use Cerad\Bundle\LevelBundle\Model\LevelRepositoryInterface;

class LevelRepository implements LevelRepositoryInterface
{
    protected $items = array();
    
    public function __construct($files)
    {
        foreach($files as $file)
        {
            $configs = Yaml::parse(file_get_contents($file));
            
            foreach($configs as $id => $config)
            {
                $config['id'] = $id;
                $item = new Level($config);
                $this->items[$item->getId()] = $item;
            }
        }
    }
    public function find($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }
    public function findAll()
    {
        return $this->items;        
    }
}

?>
