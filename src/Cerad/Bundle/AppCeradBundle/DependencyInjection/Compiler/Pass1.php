<?php

namespace Cerad\Bundle\AppCeradBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/* =======================================================
 * Just keeping this as an example of how to install a pass
 */
class Pass1 implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        return;
        
        $mailerDriverId = $container->getParameter('my_scope.mailer.driver');
        
        $def = $container->getDefinition('cerad_app_cerad.persons.import_yaml');
 
        $def->addArgument(new Reference($mailerDriverId));
        $def->addMethodCall('setDriver', array(new Reference($mailerDriverId)));
    }
}