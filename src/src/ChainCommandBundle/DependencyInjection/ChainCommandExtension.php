<?php

namespace App\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * Setup ChainCommand configs
 *
 * Class ChainCommandExtension
 * @package App\ChainCommandBundle\DependencyInjection
 */
class ChainCommandExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = Yaml::parse(
            file_get_contents(__DIR__.'/../Resource/config/config.yml')
        );

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resource/config'));
        $loader->load('service.yml');

        $container->setParameter('chain_command.chains', $config['chains']);
    }
}
