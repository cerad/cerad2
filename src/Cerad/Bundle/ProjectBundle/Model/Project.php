<?php
namespace Cerad\Bundle\ProjectBundle\Model;

class Project
{
    protected $id;
    protected $slugs;
    protected $status;
    protected $verified;
                
    protected $fedId;     // AYSO
    protected $fedRoleId;  // AYSOV
    
    protected $modelId;  // ayso_natgames, ussf_alabama
    
    protected $title;
    protected $desc;
    
    protected $submit;
    protected $prefix;
    protected $assignor;
    
    public function getId      () { return $this->id;       }
    public function getSlugs   () { return $this->slugs;    }
    public function getStatus  () { return $this->status;   }
    public function getVerified() { return $this->verified; }
    
    public function getFedId     () { return $this->fedId;     }
    public function getFedRoleId () { return $this->fedRoleId; }
    public function getModelId   () { return $this->modelId;   }
    
    public function getDesc () { return $this->desc;  }
    public function getTitle() { return $this->title; }
    
    public function getSubmit()   { return $this->submit; }
    public function getPrefix()   { return $this->prefix; }
    public function getAssignor() { return $this->assignor; }
    
    public function getBasic() { return $this->basic; }
    
    public function __construct($config)
    {   
        $info = $config['info'];
        // Take whatever we have and apply it
        foreach($info as $propName => $propValue)
        {
            $this->$propName = $propValue;
        }
        unset($config['info']);
        foreach($config as $name => $value)
        {
            $this->$name = $value;
        }
    }
    // Just retrun first slug in list
    public function getSlug() { return $this->slugs[0]; }
}
?>
