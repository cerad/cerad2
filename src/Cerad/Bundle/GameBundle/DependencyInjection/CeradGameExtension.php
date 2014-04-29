<?php

namespace Cerad\Bundle\GameBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CeradGameExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        $loader->load('services/services2.yml');
        $loader->load('services/game.yml');
        $loader->load('services/assign.yml');
        $loader->load('services/results.yml');
        
        $gameResourceDir = __DIR__ . '/../Resources';
        
        $container->setParameter('cerad_game__resources_dir',$gameResourceDir);
        $container->setParameter('cerad_game__game_official__assign__workflow__file', $gameResourceDir . '/config/workflows/assign.yml');
        
     }
}
