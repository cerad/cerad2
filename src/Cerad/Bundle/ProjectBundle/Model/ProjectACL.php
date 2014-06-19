<?php

namespace Cerad\Bundle\ProjectBundle\Model;

/* ==================================================
 * 19 June 2014
 * Probably does not belong in the model layer but controls global project access
 */
class ProjectACL
{
    protected $acl;
    protected $securityContext;
    
    public function __construct($securityContext,$acl)
    {
        $this->acl = $acl;
        $this->securityContext = $securityContext;
    }
}
?>
