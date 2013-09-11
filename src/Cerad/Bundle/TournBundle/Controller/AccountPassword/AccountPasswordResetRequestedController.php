<?php

namespace Cerad\Bundle\TournBundle\Controller\AccountPassword;

use Cerad\Bundle\TournBundle\Controller\BaseController as MyBaseController;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\EqualTo  as EqualToConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

class AccountPasswordResetRequestedController extends MyBaseController
{
    public function requestedFormAction(Request $request, $id)
    {
        $userId = $id;
        
        if (!$userId) return $this->redirect('cerad_tourn_home');
        
        $model = $this->getModel($userId);
        
        $form  = $this->getModelForm($model);
                
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model1 = $form->getData();
            
            //$model2 = $this->processRequestModel($model1);
            
            //$this->sendEmail($model2);
            
            //$user = $model2['user'];
            
            //return $this->redirect('cerad_tourn_password_reset_requested',array('id' => $user->getId()));
        }
        
        // Render
        $tplData = array();
        $tplData['form'] = $form->createView();
        
        return $this->render('@CeradTourn/AccountPassword/ResetRequested/AccountPasswordResetRequestedIndex.html.twig',$tplData);      
    }
    protected function processModel($model)
    {
        $username = $model['username'];
        
        $userProvider = $this->get('cerad_user.user_provider');
        
        $user = $userProvider->loadUserByUsername($username);
        
        // Make a key 
        $token = rand(1000,9999);
        $user->setPasswordResetToken($token);
        
        $userManager = $userProvider->getUserManager();
        $userManager->updateUser($user);
        
        $model['user']     = $user;
        $model['token']    = $token;
        $model['tokenx']   = $token;
        $model['password'] = null;
        
        return $model;
    }
    protected function getModel($userId)
    {
        $userManager = $this->get('cerad_user.user_manager');
        $user = $userManager->find($userId);
        
        if (!$user) throw new \Exception("User not found for password reset requested");
        
        $model = array();
        $model['user']     = $user;
        $model['token']    = null;
        $model['password'] = null;
        
        return $model;
    }
    protected function getModelForm($model)
    {
        $equalToConstraintOptions = array(
            'value' => $user->getPasswordResetToken(),
            'message' => 'Invalid token value',
        );
        
        $builder = $this->createFormBuilder($model);

        $builder->add('token','text', array(
            'required' => true,
            'label'    => 'Password Reset Token (4 digits)',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
                new EqualToConstraint($equalToConstraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
        $builder->add('password', 'repeated', array(
            'type'     => 'password',
            'label'    => 'Zayso Password',
            'required' => true,
            'attr'     => array('size' => 20),
            
            'invalid_message' => 'The password fields must match.',
            'constraints'     => new NotBlankConstraint(),
            'first_options'   => array('label' => 'New Password'),
            'second_options'  => array('label' => 'New Password(confirm)'),
            
            'first_name'  => 'pass1',
            'second_name' => 'pass2',
        ));
        return $builder->getForm();
    }
}
?>
