<?php
namespace Cerad\Bundle\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Cerad\Bundle\UserBundle\Model\UserManager as UserManagerModel;

use Cerad\Bundle\UserBundle\Entity\UserRepository as UserEntityRepository;

class UserManager extends UserManagerModel
{       
    protected $userPepository;
    
    public function __construct
    (
        EncoderFactoryInterface $encoderFactory,
        UserEntityRepository    $userRepository
    )
    {
        parent::__construct($encoderFactory);
        $this->userRepository = $userRepository;
    }
}
?>
