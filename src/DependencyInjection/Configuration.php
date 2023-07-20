<?php

namespace Kira0269\LogViewerBundle\DependencyInjection;

use Kira0269\LogViewerBundle\LogMetric\LogMetrics;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private string $defaultLogsDir;
    private string $defaultDateFormat = 'Y-m-d';

    public function __construct(string $defaultLogsDir)
    {
        $this->defaultLogsDir = $defaultLogsDir;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kira_log_viewer');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('logs_dir')->defaultValue($this->defaultLogsDir)->end()
                ->arrayNode('file_pattern')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('date_format')->defaultValue($this->defaultDateFormat)->end()
                    ->end()
                ->end()
                ->scalarNode('log_pattern')->defaultValue('.*')->end()
                ->arrayNode('groups')
                    ->canBeUnset()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('regex')->end()
                            ->scalarNode('width')->end()
                            ->scalarNode('visible')->end()
                            ->scalarNode('type')
                                ->defaultValue('text')
                                ->validate()
                                    ->ifNotInArray(['text', 'json', 'date', 'visible'])
                                    ->thenInvalid('Invalid type %s')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('dashboard')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('date')->defaultValue('today')->end()
                        ->integerNode('metrics_per_row')->defaultValue(2)->isRequired()->end()
                        ->arrayNode('metrics')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('title')->defaultValue('Errors')->end()
                                    ->scalarNode('type')
                                        ->defaultValue('counter')
                                        ->validate()
                                            ->ifNotInArray(array_keys(LogMetrics::METRIC_TYPES))
                                            ->thenInvalid('Invalid metric filter type %s')
                                        ->end()
                                    ->end()
                                    ->scalarNode('icon')->defaultValue('')->end()
                                    ->scalarNode('color')->defaultValue('')->end()
                                    ->arrayNode('filters')
                                        ->arrayPrototype()
                                            ->scalarPrototype()->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
