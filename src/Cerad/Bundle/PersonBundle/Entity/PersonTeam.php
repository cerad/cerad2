<?php
namespace Cerad\Bundle\PersonBundle\Entity;

use Cerad\Bundle\PersonBundle\Model\PersonTeam as PersonTeamModel;;

/* ===========================================================
 * 08 June 2014
 * Late add
 */
class PersonTeam extends PersonTeamModel
{
    protected $id;

    public function __construct()
    {
        parent::__construct(); 
    }
    public function getId() { return $this->id; }
}
?>
