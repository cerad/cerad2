<?php
namespace Cerad\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\SecurityContextInterface;

/* ==============================================================
 * Used to inject the current user into a service
 * No real need to proxy the security context
 */
class UserCurrent
{
    static public function get(SecurityContextInterface $securityContext)
    {
        $token = $securityContext->getToken();
        if (!$token) return null;

        $user = $token->getUser();
        if (!is_object($user)) return null;
        
        return $user;
    }
}
?>
