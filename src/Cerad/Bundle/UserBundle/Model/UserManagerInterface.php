<?php
namespace Cerad\Bundle\UserBundle\Model;

use Cerad\Bundle\UserBundle\Model\UserInterface;

interface UserManagerInterface
{
    // returns UserInterface
    public function createUser();
    
    // Encodes password and does canonical stuff
    public function updateUser(UserInterface $user, $commit = true);
    
}

?>
