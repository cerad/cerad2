<?php
namespace Cerad\Bundle\ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\ORM\Query;

use Cerad\Bundle\PersonBundle\Entity\PersonRepository;

class PersonController extends Controller
{    
    protected $personRepo;
    
    public function __construct(PersonRepository $personRepo)
    {
        $this->personRepo = $personRepo;
    }
    protected function findPerson($personId)
    {
        if (!$personId) return null;
        
        // Optimize a bit by returning an array
        $qb = $this->personRepo->createQueryBuilder('person');
        
        $qb->andWhere('person.id = :personId');
        $qb->setParameter('personId',$personId);
        
        $qb->addSelect('fed,cert,org');

        $qb->leftJoin ('person.feds','fed');
        $qb->leftJoin ('fed.certs',  'cert');
        $qb->leftJoin ('fed.orgs',   'org');
        
        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        
    }
    public function getAction(Request $request, $personId)
    {   
        $person = $this->findPerson($personId);
        if (!$person)
        {
            return new JsonResponse(array('message' => 'Not Found'),404);
        }
      //print_r($person); die();
        $personData = array(
            'id'         => $person['id'],
            'guid'       => $person['guid'],
            'name_full'  => $person['nameFull'],
            'name_first' => $person['nameFirst'],
            'name_last'  => $person['nameLast'],
            'dob'        => $person['dob']->format('Y-m-d'),
            
            // Probably should be a loop
            'feds'       => $person['feds'],
        );
        return new JsonResponse($personData);
        
        $response = new JsonResponse();  // public function __construct($data = null, $status = 200, $headers = array())
        $response->setData($personData);
        return $response;
    }
}
?>
