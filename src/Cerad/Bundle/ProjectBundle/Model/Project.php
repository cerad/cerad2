<?php
namespace Cerad\Bundle\ProjectBundle\Model;

class Project
{
    protected $id;
    protected $slug;    // test01
    protected $slugx;   // test01-2013
    protected $status;
    protected $verified;
                
    protected $fedId;     // AYSO
    protected $fedOffId;  // AYSOV
                
    protected $title;
    protected $desc;
    
    public function getId      () { return $this->id;       }
    public function getSlug    () { return $this->slug;     }
    public function getSlugx   () { return $this->slugx;    }
    public function getStatus  () { return $this->status;   }
    public function getVerified() { return $this->verified; }
    
    public function getFedId   () { return $this->fedId;    }
    public function getFedOffId() { return $this->fedOffId; }
    
    public function getDesc       () { return $this->desc;  }
    public function getTitle      () { return $this->title; }
    
    public function __construct($config)
    {   
        $info = $config['info'];
        
        // Take whatever we have and apply it
        foreach($info as $propName => $propValue)
        {
            $this->$propName = $propValue;
        }
    }
}
?>
