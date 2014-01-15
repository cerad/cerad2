<?php
namespace Cerad\Bundle\EaysoBundle\InMemory;

use Symfony\Component\Yaml\Yaml;

class CertRepository 
{
    protected $certs = array();
    protected $certDescs = null;
    
    public function __construct($file)
    {
        $yaml = Yaml::parse(file_get_contents($file));
        $this->certs = $yaml['certs'];
    }
    public function findByCertDesc($certDesc)
    {
        // Transform is necessary
        if (!$this->certDescs)
        {
            $certDescs = array();
            foreach($this->certs as $role => $badges)
            {
                foreach($badges as $badge => $descs)
                {
                    foreach($descs as $desc)
                    {
                        $certDescs[$desc][$role] = $badge;
                    }
                }
            }
            $this->certDescs = $certDescs;
        }
        // Need to handle comma delimited nonsense
        $parts = explode(',',$certDesc);
        $certs = array();
        foreach($parts as $part)
        {
            $part = trim($part);
            if (isset($this->certDescs[$part])) $certs = array_merge($certs,$this->certDescs[$part]);
            else
            {
                echo sprintf("*** No cert record for '%s'\n",$certDesc);die();
            }
        }
        return $certs;
    }
    // Referee Intermediate National
    public function compareBadges($role,$badge1,$badge2)
    {
        if ($badge1 == $badge2) return 0;
        
        $badges = $this->certs[$role];
        foreach($badges as $badge => $descs)
        {
            if ($badge == $badge1) return -1;
            if ($badge == $badge2) return  1;
        }
    }
}

?>
