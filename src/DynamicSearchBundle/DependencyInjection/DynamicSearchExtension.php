<?php

namespace DynamicSearchBundle\DependencyInjection;

use DynamicSearchBundle\Document\Definition\DocumentDefinitionBuilderInterface;
use DynamicSearchBundle\Factory\ContextDefinitionFactory;
use DynamicSearchBundle\Paginator\Paginator;
use DynamicSearchBundle\Provider\Extension\ProviderConfig;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use DynamicSearchBundle\Configuration\Configuration as BundleConfiguration;

class DynamicSearchExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.yml');

        $this->setupConfiguration($container, $config);
        $this->setupProviderBundles($container);

    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function setupConfiguration(ContainerBuilder $container, array $config)
    {
        $contextConfig = $config['context'];

        unset($config['context']);

        $configManagerDefinition = $container->getDefinition(BundleConfiguration::class);
        $configManagerDefinition->addMethodCall('setConfig', [$config]);

        $container->setParameter('dynamic_search.context.full_configuration', $contextConfig);
        $container->setParameter('dynamic_search_default_paginator_class', Paginator::class);

        $contextDefinitionFactory = $container->getDefinition(ContextDefinitionFactory::class);

        foreach ($contextConfig as $contextName => $config) {
            $contextDefinitionFactory->addMethodCall('addContextConfig', [$contextName, $config]);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function setupProviderBundles(ContainerBuilder $container)
    {
        $providerConfig = new ProviderConfig();

        $providerConfigDefinition = new Definition();
        $providerConfigDefinition->setClass(ProviderConfig::class);

        $container->setDefinition(ProviderConfig::class, $providerConfigDefinition);

        $container
            ->registerForAutoconfiguration(DocumentDefinitionBuilderInterface::class)
            ->addTag('dynamic_search.document_definition_builder');

        if ($providerConfig->configFileExists()) {
            $container->addResource(new FileResource($providerConfig->locateConfigFile()));
        }
    }
}
