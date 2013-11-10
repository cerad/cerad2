<?php
namespace Cerad\Bundle\TournBundle\Controller\AccountUser;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

class AccountUserLoginController extends MyBaseController
{
    public function loginFormAction(Request $request)
    {
        $model = $this->createAccountUserLoginModel($request);
        
        $form = $this->createFormForModel($model); //->createForm();
        
        // The actual login is done by the login-chack listener
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['error'] = $model['loginError'];
        
        return $this->render('@CeradTourn/AccountUser/Login/AccountUserLoginForm.html.twig',$tplData);      
    }
    public function loginAction(Request $request)
    {
        
        $model = $this->createAccountUserLoginModel($request);
        
        $form = $this->createFormForModel($model); //->createForm();
        
        // The actual login is done by the login-chack listener
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        $tplData['error'] = $model['loginError'];
        
        return $this->render('@CeradTourn/AccountUser/Login/AccountUserLoginIndex.html.twig',$tplData);      
    }
    /* ================================================
     * Create the model
     */
    protected function createAccountUserLoginModel($request)
    {
         // Majic to get any previous errors
        $info = $this->getAuthenticationInfo($request);
        
        $model = array(
            'loginError'  => $info['error'],
            'username'    => $info['lastUsername'],
            'password'    => null,
            'remember_me' => true,
        );
        return $model;
    }
    /* ================================================
     * Create the form
     */
    protected function createFormForModel($model)
    {        
        /* ======================================================
         * Start building
         */
        $formOptions = array(
            'cascade_validation' => true,
            'intention' => 'authenticate',
            'csrf_protection' => false,
        );
        $constraintOptions = array(); // array('groups' => 'basic');
        
        // Bit confusing, thi is actually a named form
        // But can still be used as a builder?
        $builder = $this->get('form.factory')->createNamed('cerad_tourn_account_user_login','form',$model,$formOptions);
        
        $builder->add('username','text', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new UsernameOrEmailExistsConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
         $builder->add('password','password', array(
            'required' => true,
            'label'    => 'Password',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('remember_me','checkbox',  array('label' => 'Remember Me'));
 
        return $builder;
    }
    
    /* ================================================
     * In case the firewall is not configured correctly
     */
    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }
    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

}
?>
