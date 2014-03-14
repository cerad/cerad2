<?php

namespace Cerad\Bundle\AppCeradBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Cerad\Bundle\AppCeradBundle\DependencyInjection\Compiler\Pass1;

class CeradAppCeradBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Pass1());
    }

}
