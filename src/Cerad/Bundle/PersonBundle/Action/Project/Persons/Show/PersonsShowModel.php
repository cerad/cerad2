<?php

namespace Cerad\Bundle\PersonBundle\Action\Project\Persons\Show;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class PersonsShowModel extends ActionModelFactory
{   
    public $_route;
    public $_project;
    public $_template;
    
    public $project;
    public $criteria;
    
    protected $session;
    protected $sessionName = 'ProjectPersonsShow';
    
    protected $userRepo;
    protected $personRepo;
    protected $projectPersonRepo;
    protected $orgKeyDataTransformer;
    
    protected $roleHeirarchy;
    protected $roles;
    
    public function __construct(
        $personRepo,$projectPersonRepo,$userRepo,
        $orgKeyDataTransformer,
        $roleHeirarchy,$roles)
    {   
        $this->userRepo              = $userRepo;
        $this->personRepo            = $personRepo;
        $this->projectPersonRepo     = $projectPersonRepo;
        $this->orgKeyDataTransformer = $orgKeyDataTransformer;
        
        $this->roles = $roles;
        $this->roleHeirarchy = $roleHeirarchy;
        
    }
    public function process($formData)
    {   
        // formData == posted criteria
        $this->session->set($this->sessionName,$formData);
    }
    public function create(Request $request)
    {       
        $requestAttrs = $request->attributes;
        
        $this->_route    = $requestAttrs->get('_route');
        $this->_project  = $requestAttrs->get('_project');
        $this->_template = $requestAttrs->get('_template');
        
        $this->project = $requestAttrs->get('project');
        $this->session = $session = $request->getSession();
        
        $criteria = array(
            'roles' => 'All Roles',
        );
        
        // Merge form session
        if ($session->has($this->sessionName))
        {
            $criteria = array_merge($criteria,$session->get($this->sessionName));
        }
        $this->criteria = $criteria;
        
        
        return $this;
    }
    public function loadProjectPersons()
    {
        $criteria  = $this->criteria;
        
        // User Roles
        if ($criteria['roles'] != 'ROLE_NONE')
        {
            return $this->loadProjectPersonsForRoles($criteria);
        }
        unset($criteria['roles']);
        
        // Must have selected something
        $something = false;
        foreach($criteria as $value)
        {
            if ($value) $something = true;
        }
        if (!$something) return array();
    }
    public function loadProjectPersonsForRoles($criteria = null)
    {
        // Find users for roles
        $roleTarget = $criteria['roles'];
        $users = $this->userRepo->findAll();
        $personUser = array();
        $personKeys = array();
        foreach($users as $user)
        {
            if ($this->hasRole($user->getRoles(),$roleTarget))
            {
                if ($user->getPersonKey()) 
                {
                    $personKeys[] = $personKey = $user->getPersonKey();
                    
                    $personUser[$personKey] = $user;
                }
            }
        }
        if (count($personKeys) < 1) return array();
        
        $projectPersons = $this->projectPersonRepo->findAllByProjectPersonKeys($this->project,$personKeys);
        
        foreach($projectPersons as $projectPerson)
        {
            $person = $projectPerson->getPerson();
            $person->setUser($personUser[$person->getKey()]);
            
            $fed = $person->getProjectFed();
            $fed->sar = $this->orgKeyDataTransformer->transform($fed->getOrgKey());
        }
        // Done
        return $projectPersons; if ($criteria);
    }
    protected function hasRole($roleNames,$roleTarget)
    {
        $roleObjects = array();
        foreach($roleNames as $roleName)
        {
            $roleObjects[] = is_object($roleName) ? $roleName : new Role($roleName);
        }
        $reachableRoles = $this->roleHeirarchy->getReachableRoles($roleObjects);
        
        foreach($reachableRoles as $role)
        {
            if ($role->getRole() == $roleTarget) return true;
        }
        return false;
    }

}