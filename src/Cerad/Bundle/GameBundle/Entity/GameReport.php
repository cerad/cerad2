<?php

namespace Cerad\Bundle\GameBundle\Entity;

/* ==============================================
 * Try seeing how this works out for reporting
 */
class GameReport extends AbstractEntity
{
    protected $game;
    
    protected $text;
    
    protected $status;
    
    public function getId()      { return $this->id;      }
    public function getText()    { return $this->text;    }
    public function getGame()    { return $this->game;    }
    public function getStatus()  { return $this->status;  }
    
    public function setText    ($value) { $this->onPropertySet('text',    $value); }
    public function setGame    ($value) { $this->onPropertySet('game',    $value); }
    public function setStatus  ($value) { $this->onPropertySet('status',  $value); }
    
    static function getPropNames()
    {
        return array(
            'status','text',
        );
    }
    public function __construct($config = null)
    {
        if (!is_array($config)) return;
        
        foreach(self::getPropNames() as $propName)
        {
            if (isset($config[$propName])) $this->$propName = $config[$propName];
        }
    }
    public function getData()
    {
        $data = array();
        foreach(self::getPropNames() as $propName)
        {
            if (isset($this->$propName)) $data[$propName] = $this->$propName;
        }
        return $data;
    }    
}
?>
