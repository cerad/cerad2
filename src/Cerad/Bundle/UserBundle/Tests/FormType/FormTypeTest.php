<?php

namespace Cerad\Bundle\UserBundle\Tests\Entity\UserManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Component\Form\Form;

//  Cerad\Bundle\UserBundle\Form\Type\UsernameUniqueFormType;
//  Cerad\Bundle\UserBundle\Form\Type\UsernameExistsFormType;

class FormTest extends WebTestCase
{
    protected static $client;
    protected static $container;
 
    public static function setUpBeforeClass()
    {
        self::$client    = static::createClient();
        self::$container = self::$client->getContainer();
        
    }
    /* ==========================================================
     * Just checking to see if the form can be created from the form type
     * setDefaultsOptions is called so all the constraint classes are checked
     */
    public function testFormTypes()
    {
        $container = self::$container;
        $formFactory = $container->get('form.factory');
        
        $form1 = $formFactory->create($container->get('cerad_user.username_unique.form_type'));
        $this->assertTrue($form1 instanceOf Form);
        
        $form2 = $formFactory->create($container->get('cerad_user.username_exists.form_type'));
        $this->assertTrue($form2 instanceOf Form);
        
        $form3 = $formFactory->create($container->get('cerad_user.email_unique.form_type'));
        $this->assertTrue($form3 instanceOf Form);
        
        $form4 = $formFactory->create($container->get('cerad_user.email_exists.form_type'));
        $this->assertTrue($form4 instanceOf Form);
        
        $form5 = $formFactory->create($container->get('cerad_user.username_and_email_unique.form_type'));
        $this->assertTrue($form5 instanceOf Form);
        
        $form6 = $formFactory->create($container->get('cerad_user.username_or_email_exists.form_type'));
        $this->assertTrue($form6 instanceOf Form);
        
    }
}
