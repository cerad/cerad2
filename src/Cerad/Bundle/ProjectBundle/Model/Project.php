<?php
namespace Cerad\Bundle\ProjectBundle\Model;

/* =====================================================
 * TODO: Change fedId and fedRoleId to fed and fedRole
 */
class Project
{
    protected $id;
    
    protected $slug;
    
    protected $slugs; // TODO: Remove after s1games
    
    protected $status;
    protected $verified;
                
    protected $fedId;     // AYSO
    protected $fedRoleId; // AYSOV
    
    protected $results;  // s1games, s5games, natgames etc
    
    protected $title;
    protected $desc;
    
    protected $submit;
    protected $prefix;
    protected $assignor;
    
    protected $basic;
    protected $searches;
    
    public function getId      () { return $this->id;       }
    public function getKey     () { return $this->id;       }
    public function getSlugx   () { return $this->slug;     }
    public function getSlugs   () { return $this->slugs;    }
    public function getStatus  () { return $this->status;   }
    public function getVerified() { return $this->verified; }
    
    public function getFed     () { return $this->fedId;     }
    public function getFedRole () { return $this->fedRoleId; }
    
    public function getFedId     () { return $this->fedId;     }
    public function getFedRoleId () { return $this->fedRoleId; }
    public function getResults   () { return $this->results;   }
    
    public function getDesc () { return $this->desc;  }
    public function getTitle() { return $this->title; }
    
    public function getSubmit()   { return $this->submit; }
    public function getPrefix()   { return $this->prefix; }
    public function getAssignor() { return $this->assignor; }
    
    public function getBasic() { return $this->basic; }
    
    public function getSearches() { return $this->searches; }
    
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
    public function isActive() { return ('Active' == $this->status) ? true: false; }
    
    /* =====================================================
     * Want to stay backwards compatible for just a bit
     * AppTourns needs this
     */
    public function getSlug()
    {
        if ($this->slug) return $this->slug;
        
        if (is_array($this->slugs)) return $this->slugs[0];
        
        return null;
    }
}
?>
