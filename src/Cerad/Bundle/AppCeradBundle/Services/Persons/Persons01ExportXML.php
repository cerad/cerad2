<?php
namespace Cerad\Bundle\AppCeradBundle\Services\Persons;

class Persons01ExportXML
{
    protected $conn;
    protected $writer;
    
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    /* =================================================================
     * Organizations for a person fed
     */
    protected function processPersonFedOrgs($conn,$writer,$personFedId)
    {
        $writer->startElement('person_fed_orgs');
        
        $sql = "SELECT person_fed_orgs.* FROM person_fed_orgs WHERE fed_id = :personFedId;";
        
        $rows = $conn->fetchAll($sql,array('personFedId' => $personFedId));
        foreach($rows as $row)
        {
            $writer->startElement('person_fed_org');
            
            $writer->writeAttribute('role',     $row['role']);
            $writer->writeAttribute('org_id',   $row['org_id']);
            
            $writer->writeAttribute('mem_year',   $row['mem_year']);
            $writer->writeAttribute('mem_last',   $row['mem_last']);
            $writer->writeAttribute('mem_first',  $row['mem_first']);
            $writer->writeAttribute('mem_expires',$row['mem_expires']);
            
            $writer->writeAttribute('bc_year',   $row['bc_year']);
            $writer->writeAttribute('bc_last',   $row['bc_last']);
            $writer->writeAttribute('bc_first',  $row['bc_first']);
            $writer->writeAttribute('bc_expires',$row['bc_expires']);
            
            $writer->writeAttribute('status',   $row['status']);
            $writer->writeAttribute('verified', $row['verified']);
            
            $writer->endElement();
        }
        $writer->endElement();
    }
    /* =================================================================
     * Certs for a person fed
     */
    protected function processPersonFedCerts($conn,$writer,$personFedId)
    {
        $writer->startElement('person_fed_certs');
        
        $sql = "SELECT person_fed_cert.* FROM person_fed_certs AS person_fed_cert WHERE fed_id = :personFedId;";
        
        $rows = $conn->fetchAll($sql,array('personFedId' => $personFedId));
        foreach($rows as $row)
        {
            $writer->startElement('person_fed_cert');
            
            $writer->writeAttribute('role',     $row['role']);
            $writer->writeAttribute('badge',    $row['badge']);
            $writer->writeAttribute('badgex',   $row['badgex']);
            
            $writer->writeAttribute('date_cert',    $row['date_cert']);
            $writer->writeAttribute('date_upgraded',$row['date_upgraded']);
            $writer->writeAttribute('date_expires', $row['date_expires']);
            
            $writer->writeAttribute('upgrading',$row['upgrading']);
            $writer->writeAttribute('status',   $row['status']);
            $writer->writeAttribute('verified', $row['verified']);
            
            $writer->endElement();
        }
        $writer->endElement(); // PersonFeds
    }
    protected function processPersonFeds($conn,$writer,$personId)
    {
        $writer->startElement('person_feds');
        
        $sql = "SELECT person_fed.* FROM person_feds AS person_fed WHERE person_id = :personId;";
        
        $rows = $conn->fetchAll($sql,array('personId' => $personId));
        foreach($rows as $row)
        {
            $writer->startElement('person_fed');
            
            $writer->writeAttribute('fed_id',     $row['id']);
            $writer->writeAttribute('fed_role_id',$row['fed_role_id']);
            $writer->writeAttribute('status',     $row['status']);
            $writer->writeAttribute('verified',   $row['verified']);

            $this->processPersonFedCerts($conn,$writer,$row['id']);
            $this->processPersonFedOrgs ($conn,$writer,$row['id']);
            
            $writer->endElement();
        }
        $writer->endElement(); // PersonFeds
    }
    protected function processPerson($conn,$writer,$person)
    {
        $writer->startElement('person');
            
        $writer->writeAttribute('id',       $person['id']);
        $writer->writeAttribute('guid',     $person['guid']);
        $writer->writeAttribute('name_full',$person['name_full']);
           
        $this->processPersonFeds($conn,$writer,$person['id']);
        
        $writer->endElement(); // Person
    }
    protected function processPersons($conn,$writer)
    {   
        $sql = "SELECT person.* FROM persons AS person ORDER BY person.id;";
        
        $rows = $conn->fetchAll($sql);
        
        foreach($rows as $row)
        {
            $this->processPerson($conn,$writer,$row);
          //print_r($row); die();
        }
    }
    public function process()
    {
        $this->writer = $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');
        
        $writer->startElement('export');
        $writer->writeAttribute('Name','S5Games');
        
        $writer->startElement('persons');
        
        $this->processPersons($this->conn,$writer);
        
        $writer->endElement(); // Persons
        
        $writer->endElement(); // Export

        $writer->endDocument();
        echo $writer->outputMemory(true);        
    }
}
?>
