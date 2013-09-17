<?php
namespace Cerad\Bundle\TournBundle\Controller\PersonPerson;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\Validator\Constraints\Email     as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

/* =========================================================
 * Just a redirect to PersonUpdate for now
 */
class PersonPersonUpdateController extends MyBaseController
{
    public function updateAction(Request $request, $id = 0)
    {
        // Document
        $personPersonId = $id;
        
        // Security
        if (!$this->hasRoleUser()) return $this->redirect('cerad_tourn_welcome');
        
        // Should have an id
        if (!$personPersonId) return $this->redirect('cerad_tourn_home');
        
        // Cheat for now and just redirect to the person updater
        $personRepo = $this->get('cerad_person.person_repository');
        $personPerson = $personRepo->findPersonPerson($personPersonId);
        $person = $personPerson->getChild();
        
        return $this->redirect('cerad_tourn_person_update',array('id' => $person->getId()));
    }
}
