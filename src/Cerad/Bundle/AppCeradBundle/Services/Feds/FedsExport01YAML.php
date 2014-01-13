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
     * Certs
     */
    protected function processFedCerts($fedKey)
    {
        $sql = <<<EOT
SELECT
    cert.role      AS role,
    cert.badge     AS badge,
    cert.badgex    AS badge_user,
    cert.upgrading AS upgrading
                
FROM  person_fed_certs AS cert
WHERE fed_id = :fedKey 
ORDER BY role
EOT;
        $rows = $this->conn->fetchAll($sql,array('fedKey' => $fedKey));
        
        return $rows;
    }
    /* =======================================================
     * Feds collection
     */
    protected function processFeds()
    {
        $sql = <<<EOT
SELECT 
    fed.person_id   AS person_id,
    fed.id          AS fed_key,
    fed.fed_role_id AS fed_role,
    org.org_id      AS org_key,
    org.mem_year    AS mem_year
                
FROM      person_feds AS fed
LEFT JOIN person_fed_orgs AS org ON org.fed_id = fed.id
ORDER BY  person_id
EOT;
      //$sql .= "\nLIMIT 0,3";
        $sql .= ";\n";
        
        $rows = $this->conn->fetchAll($sql);
        
        $items = array();
        
        foreach($rows as $item)
        {
            $id = $item['fed_key']; // AYSOV12341234
            
            $item['certs']  = $this->processFedCerts($id);
            
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
