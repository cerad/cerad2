<?php
namespace Cerad\Bundle\ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
//  Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\ORM\Query;

use Cerad\Bundle\PersonBundle\Entity\PersonRepository;

class PersonsController extends Controller
{    
    protected $personRepo;
    
    public function __construct(PersonRepository $personRepo)
    {
        $this->personRepo = $personRepo;
    }
    protected function findPersons()
    {   
        // Optimize a bit by returning an array
        $qb = $this->personRepo->createQueryBuilder('person');
           
        $qb->addSelect('fed,cert,org');

        $qb->leftJoin ('person.feds','fed');
        $qb->leftJoin ('fed.certs',  'cert');
        $qb->leftJoin ('fed.orgs',   'org');
        
        $qb->orderBy('person.nameFull');
        
        return $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);
        
    }
    protected function extractPersonData($person)
    {
        $personData = array(
            'id'         => $person['id'],
            'guid'       => $person['guid'],
            'name_full'  => $person['nameFull'],
            'name_first' => $person['nameFirst'],
            'name_last'  => $person['nameLast'],
            'dob'        => $person['dob'] ? $person['dob']->format('Y-m-d') : null,
            'feds'       => $person['feds'],
        );
        
        return $personData;
    }
    public function getAction(Request $request)
    {   
        $persons = $this->findPersons();
        $personsData = array();
        $count = 0;
        foreach($persons as $person)
        {
            if ($count++ < 30)
            {
                $personsData[] = $this->extractPersonData($person);
            }
        }
        return new JsonResponse($personsData);
    
    }
}
?>
