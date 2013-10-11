<?php
namespace Cerad\Bundle\TournBundle\TwigExtension;

/* ==============================================================
 * Put the admin extension here because there is overlap between regular
 * functionality and admin specific functionality
 */
class TournAdminExtension extends \Twig_Extension
{
    protected $userRepo;
    protected $personRepo;
    
    public function getName()
    {
        return 'cerad_tourn_admin_extension';
    }
    public function __construct($userRepo,$personRepo)
    {
        $this->userRepo   = $userRepo;
        $this->personRepo = $personRepo;
    }
    public function getFunctions()
    {
        return array
        (            
            'cerad_tourn_get_user_for_person' => new \Twig_Function_Method($this, 'getUserForPerson'),            
        );
    }
    public function getUserForPerson($person)
    {
        $user1 = $this->userRepo->findOneByPersonGuid($person->getGuid());
        if ($user1) return $user1;
        
        $user2 = $this->userRepo->createUser();
        return $user2;
    }
}
?>
