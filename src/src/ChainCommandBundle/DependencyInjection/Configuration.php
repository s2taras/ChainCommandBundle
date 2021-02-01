<?php

namespace App\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for chain_command
 *
 * Class Configuration
 * @package App\ChainCommandBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('chain_command');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('chains')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('parent')->end()
                            ->arrayNode('children')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
