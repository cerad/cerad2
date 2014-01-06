<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Feds;

use Symfony\Component\Yaml\Yaml;

/* ============================================================
 * 04 Jan 2014
 * fed.id was AYSOV12341234
 */
class FedsExport01YAML
{
    protected $conn;
    protected $items;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    /* =================================================================
     * Orgs
     */
    protected function processFedOrgs($fedId)
    {
        $sql = "SELECT person_fed_orgs.* FROM person_fed_orgs WHERE fed_id = :fedId ORDER BY ROLE;";
        
        $rows = $this->conn->fetchAll($sql,array('fedId' => $fedId));
        
        $items = array();
        
        foreach($rows as $row)
        {
            unset($row['id']);
            unset($row['fed_id']);
            
            $items[] = $row;
        }
        return $items;
    }
    /* =================================================================
     * Certs
     */
    protected function processFedCerts($fedId)
    {
        $sql = "SELECT person_fed_certs.* FROM person_fed_certs WHERE fed_id = :fedId ORDER BY role;";
        
        $rows = $this->conn->fetchAll($sql,array('fedId' => $fedId));
        
        $items = array();
        
        foreach($rows as $row)
        {
            unset($row['id']);
            unset($row['fed_id']);
            
            $items[] = $row;
        }
        return $items;
    }
    /* =======================================================
     * Feds collection
     */
    protected function processFeds()
    {
      //$sql = "SELECT person_feds.* FROM person_feds ORDER BY id LIMIT 0,5;";
        $sql = "SELECT person_feds.* FROM person_feds ORDER BY person_id;";
        
        $rows = $this->conn->fetchAll($sql);
        
        $items = array();
        
        foreach($rows as $row)
        {
            $id = $row['id']; // AYSOV12341234
            
            $item = array();
            $item['fed_id']      = $id;
            $item['fed_role_id'] = $row['fed_role_id'];
            $item['person_id']   = (int)$row['person_id'];
            $item['status']      = $row['status'];
            $item['verified']    = $row['verified'];
            
            $item['certs'] = $this->processFedCerts($id);
            $item['orgs']  = $this->processFedOrgs ($id);
            
            $items[] = $item;
        }
        return $items;
    }
    /* ==========================================================================
     * Main entry point
     */
    public function process()
    {
        $this->items = array();
        
        $this->items['feds'] = $this->processFeds();
        
        return $this;
    }
    public function flush($clear=true)
    {
        return Yaml::dump($this->items,10);
    }
    public function getFedCount()
    {
        return count($this->items['feds']);
    }
}
?>
