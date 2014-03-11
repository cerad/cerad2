<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Persons;

use Symfony\Component\Yaml\Yaml;

class PersonsExport01YAML
{
    protected $conn;
    protected $items;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    /* =================================================================
     * Accounts
     */
    protected function processUsers($personGuid)
    {
        $sql = <<<EOT
SELECT
    user.person_guid      AS personGuid,
    user.person_status    AS personStatus,
    user.person_verified  AS personVerified,
    user.person_confirmed AS personConfirmed,
                
    user.id                 AS userId,
    user.username           AS username,
    user.username_canonical AS usernameCanonical,
    user.email              AS email,
    user.email_canonical    AS emailCanonical,
    user.email_confirmed    AS emailConfirmed,
                
    user.salt               AS salt,
    user.password           AS password,
    user.password_hint      AS passwordHint,
    user.roles              AS roles,
                
    user.account_name          AS accountName,
    user.account_created_on    AS accountCreatedOn,
    user.account_updated_on    AS accountUpdatedOn,
    user.account_last_login_on AS accountLastLoginOn

FROM  users AS user
WHERE person_guid = :personGuid
EOT;
        $rows = $this->conn->fetchAll($sql,array('personGuid' => $personGuid));
        
        foreach($rows as &$row)
        {
            $row['roles'] = unserialize($row['roles']);
        }
        return $rows;
    }
    /* =================================================================
     * Certs
     */
    protected function processFedCerts($fedId)
    {
        $sql = <<<EOT
SELECT
    cert.role           AS role,
    cert.role_date      AS roleDate,
    cert.badge          AS badge,
    cert.badge_date     AS badgeDate,
    cert.badge_verified AS badgeVerified,
    cert.badge_user     AS badgeUser,
    cert.upgrading      AS upgrading,
    cert.org_key        AS orgKey,
    cert.mem_year       AS memYear,
    cert.status         AS status
                
FROM  person_fed_certs AS cert
WHERE person_fed_id = :fedId 
ORDER BY role
EOT;
        $rows = $this->conn->fetchAll($sql,array('fedId' => $fedId));
        
        return $rows;
    }
    /* =======================================================
     * Feds collection
     */
    protected function processFeds($personId)
    {
        $sql = <<<EOT
SELECT 
    fed.person_id        AS personId,
    fed.person_verified  AS personVerified,
                
    fed.id               AS fedId,
    fed.fed              AS fed,
    fed.fed_role         AS fedRole,
    fed.fed_role_date    AS fedRoleDate,
    fed.fed_key          AS fedKey,
    fed.fed_key_verified AS fedKeyVerified,
    fed.org_key          AS orgKey,
    fed.org_key_verified AS orgKeyVerified,
    fed.mem_year         AS memYear,
    fed.status           AS status
                
FROM  person_feds AS fed
WHERE fed.person_id = :personId
ORDER BY  fed.id
EOT;
      //$sql .= "\nLIMIT 0,3";
        $sql .= ";\n";
        
        $rows = $this->conn->fetchAll($sql,array('personId' => $personId));
        
        foreach($rows as &$row)
        {
            $row['certs'] = $this->processFedCerts($row['fedId']);
        }
        return $rows;
    }
    /* =======================================================
     * Persons collection
     */
    protected function processPersons()
    {
        $sql = <<<EOT
SELECT 
    person.id          AS personId,
    person.guid        AS guid,
    person.name_full   AS nameFull,
    person.name_first  AS nameFirst,
    person.name_last   AS nameLast,
    person.name_nick   AS nameNick,
    person.name_middle AS nameMiddle,
    person.email       AS email,
    person.phone       AS phone,
    person.gender      AS gender,
    person.dob         AS dob,
    person.address_city    AS addressCity,
    person.address_state   AS addressState,
    person.address_zipcode AS addressZipcode,
    person.notes       AS notes,
    person.status      AS status,
    person.verified    AS verified
FROM      persons AS person
ORDER BY  person.id
EOT;
      //$sql .= "\nLIMIT 0,3";
        $sql .= ";\n";
        
        $rows = $this->conn->fetchAll($sql);
        
        foreach($rows as &$row)
        {
            $row['feds' ] = $this->processFeds ($row['personId']);
            $row['users'] = $this->processUsers($row['guid']);
        }
        return $rows;
    }
    /* ==========================================================================
     * Main entry point
     */
    public function process()
    {
        $this->items = array();
        
        $this->items['persons'] = $this->processPersons();
        
        return $this;
    }
    public function flush($clear=true)
    {
        $dump = Yaml::dump($this->items,10);
        
        if ($clear) $this->items = array();
        
        return $dump;
    }
    public function getPersonsCount()
    {
        return count($this->items['persons']);
    }
}
?>
