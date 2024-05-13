<?php


namespace Kira0269\LogViewerBundle\DependencyInjection;

use Kira0269\LogViewerBundle\LogMetric\LogMetrics;
use Kira0269\LogViewerBundle\LogParser\LogParserInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LogViewerExtension extends Extension
{
    public function getAlias(): string
    {
        return 'kira_log_viewer';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration($container->getParameter('kernel.logs_dir'));
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(LogParserInterface::class)
            ->setArgument('$logsDir', $config['logs_dir'])
            ->setArgument('$filePattern', $config['file_pattern'])
            ->setArgument('$logPattern', $config['log_pattern'])
            ->setArgument('$groupsConfig', $config['groups']);

        $container->getDefinition(LogMetrics::class)
            ->setArgument('$groups', $config['groups'])
            ->setArgument('$metricsConfig', $config['dashboard']['metrics']);

        $container->setParameter('kira_log_viewer.dashboard.metrics_per_row', $config['dashboard']['metrics_per_row']);
        $container->setParameter('kira_log_viewer.dashboard.metrics', $config['dashboard']['metrics']);
        $container->setParameter('kira_log_viewer.groups', $config['groups']);
        $container->setParameter('kira_log_viewer.dashboard.date', $config['dashboard']['date']);
    }
}
