<?php

namespace DynamicSearchBundle\DependencyInjection\Compiler;

use DynamicSearchBundle\DependencyInjection\Compiler\Helper\OptionsResolverValidator;
use DynamicSearchBundle\Factory\ContextDefinitionFactory;
use DynamicSearchBundle\Registry\ResourceNormalizerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class NormalizerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceDefinitionStack = [];
        foreach ($container->findTaggedServiceIds('dynamic_search.resource_normalizer', true) as $id => $tags) {
            $definition = $container->getDefinition(ResourceNormalizerRegistry::class);
            foreach ($tags as $attributes) {
                $serviceDefinitionStack[] = ['serviceName' => $attributes['identifier'], 'id' => $id];
                $definition->addMethodCall('registerResourceNormalizer', [new Reference($id), $attributes['identifier'], $attributes['data_provider']]);
            }
        }

        $this->validateResourceNormalizerOptions($container, $serviceDefinitionStack);

        $serviceDefinitionStack = [];
        foreach ($container->findTaggedServiceIds('dynamic_search.document_normalizer', true) as $id => $tags) {
            $definition = $container->getDefinition(ResourceNormalizerRegistry::class);
            foreach ($tags as $attributes) {
                $serviceDefinitionStack[] = ['serviceName' => $attributes['identifier'], 'id' => $id];
                $definition->addMethodCall('registerDocumentNormalizer', [new Reference($id), $attributes['identifier'], $attributes['index_provider']]);
            }
        }

        $this->validateDocumentNormalizerOptions($container, $serviceDefinitionStack);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $serviceDefinitionStack
     */
    protected function validateResourceNormalizerOptions(ContainerBuilder $container, array $serviceDefinitionStack)
    {
        if (!$container->hasParameter('dynamic_search.context.full_configuration')) {
            return;
        }

        $validator = new OptionsResolverValidator();
        $contextDefinitionFactory = $container->getDefinition(ContextDefinitionFactory::class);
        $contextConfiguration = $container->getParameter('dynamic_search.context.full_configuration');

        foreach ($contextConfiguration as $contextName => &$contextConfig) {

            if (!isset($contextConfig['data_provider']['normalizer'])) {
                continue;
            }

            $contextService = [
                'serviceName' => $contextConfig['data_provider']['normalizer']['service'] ?? null,
                'options'     => $contextConfig['data_provider']['normalizer']['options'] ?? null
            ];

            $contextConfig['data_provider']['normalizer']['options'] = $validator->validate($container, $contextService, $serviceDefinitionStack);

            $contextDefinitionFactory->addMethodCall('replaceContextConfig', [$contextName, $contextConfig]);
        }

        $container->setParameter('dynamic_search.context.full_configuration', $contextConfiguration);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $serviceDefinitionStack
     */
    protected function validateDocumentNormalizerOptions(ContainerBuilder $container, array $serviceDefinitionStack)
    {
        if (!$container->hasParameter('dynamic_search.context.full_configuration')) {
            return;
        }

        $validator = new OptionsResolverValidator();
        $contextDefinitionFactory = $container->getDefinition(ContextDefinitionFactory::class);
        $contextConfiguration = $container->getParameter('dynamic_search.context.full_configuration');

        foreach ($contextConfiguration as $contextName => &$contextConfig) {

            if (!isset($contextConfig['output_channels']) ||!is_array($contextConfig['output_channels'])) {
                continue;
            }

            foreach ($contextConfig['output_channels'] as $outputChannelName => &$outputChannelConfig) {
                $contextService = [
                    'serviceName' => $outputChannelConfig['normalizer']['service'] ?? null,
                    'options'     => $outputChannelConfig['normalizer']['options'] ?? null
                ];

                $outputChannelConfig['normalizer']['options'] = $validator->validate($container, $contextService, $serviceDefinitionStack);
            }

            $contextDefinitionFactory->addMethodCall('replaceContextConfig', [$contextName, $contextConfig]);
        }

        $container->setParameter('dynamic_search.context.full_configuration', $contextConfiguration);
    }
}
