<?php
namespace Cerad\Bundle\TournBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cerad\Bundle\UserBundle\Model\UserInterface;

class BaseController extends Controller
{
    const SESSION_PERSON_PLAN_ID    = 'cerad_tourns_person_plan_id';
    const FLASHBAG_TYPE             = 'cerad_tourns';
    const FLASHBAG_ACCOUNT_CREATED  = 'cerad_tourn_account_created';
    
    protected function punt($request,$reason = null)
    {
        $flashBag = $request->getSession()->getFlashBag();
        
        $flashBag->add(self::FLASHBAG_TYPE,$reason);
        
        return $this->redirect($this->generateUrl('cerad_tourn_welcome'));
    }
    public function redirect($path,$params = array())
    {
        return parent::redirect($this->generateUrl($path,$params));
    }
    /* ==================================================
     * Short cuts for determining users
     */
    protected function hasRoleUser($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_USER');
    }
    protected function hasRoleAdmin($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }
    protected function hasRoleAssignor($projectId = null)
    {
        return $this->get('security.context')->isGranted('ROLE_ASSIGNOR');
    }
    /* ===================================================
     * This is similiar to what the authentication listener does on success
     * This should me moved to some sort of user service
     */
    public function loginUser(Request $request, UserInterface $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $securityContext = $this->get('security.context');
        
        $securityContext->setToken($token);
        
        $session = $request->getSession();
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::LAST_USERNAME);

        /* ============================================================
         * Lots of other good stuff
         * AbstractAuthenticationListener
         */
        return;
        
        if (null !== $this->dispatcher) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
        }

        $response = $this->successHandler->onAuthenticationSuccess($request, $token);

        if (!$response instanceof Response) {
            throw new \RuntimeException('Authentication Success Handler did not return a Response.');
        }

        if (null !== $this->rememberMeServices) {
            $this->rememberMeServices->loginSuccess($request, $response, $token);
        }

    }
    /* ===================================================
     * Always have a default project
     */
    protected function getProject($slug = null)
    {
        $find = $this->get('cerad_project.find_default.in_memory');
        return $find->project;
    }
}
?>
