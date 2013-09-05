<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
          //new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Cerad\Bundle\ProjectBundle\CeradProjectBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            
            // Web tool bar
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            
            // Not sure yet
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            
            // Generate Bundle
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
