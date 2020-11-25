<?php

namespace DynamicSearchBundle\Manager;

use DynamicSearchBundle\Configuration\ConfigurationInterface;
use DynamicSearchBundle\Context\ContextDefinitionInterface;
use DynamicSearchBundle\Document\Definition\DocumentDefinition;
use DynamicSearchBundle\Exception\Resolver\DefinitionNotFoundException;
use DynamicSearchBundle\Normalizer\Resource\ResourceMetaInterface;
use DynamicSearchBundle\Resolver\DocumentDefinitionResolverInterface;

class DocumentDefinitionManager implements DocumentDefinitionManagerInterface
{
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var DocumentDefinitionResolverInterface
     */
    protected $documentDefinitionResolver;

    /**
     * @param ConfigurationInterface              $configuration
     * @param DocumentDefinitionResolverInterface $documentDefinitionResolver
     */
    public function __construct(
        ConfigurationInterface $configuration,
        DocumentDefinitionResolverInterface $documentDefinitionResolver
    ) {
        $this->configuration = $configuration;
        $this->documentDefinitionResolver = $documentDefinitionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function generateDocumentDefinition(ContextDefinitionInterface $contextDefinition, ResourceMetaInterface $resourceMeta)
    {
        try {
            $documentDefinitionBuilderStack = $this->documentDefinitionResolver->resolve($contextDefinition->getName(), $resourceMeta);
        } catch (DefinitionNotFoundException $e) {
            return null;
        }

        $documentDefinition = new DocumentDefinition($contextDefinition->getResourceNormalizerName());

        foreach ($documentDefinitionBuilderStack as $documentDefinitionBuilder) {
            $documentDefinitionBuilder->buildDefinition($documentDefinition, $resourceMeta->getNormalizerOptions());
        }

        return $documentDefinition;
    }
}
